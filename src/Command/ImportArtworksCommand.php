<?php

namespace App\Command;

use App\Entity\Artist;
use App\Entity\Artwork;
use App\Entity\Category;
use App\Entity\Style;
use App\Repository\ArtistRepository;
use App\Repository\ArtworkRepository;
use App\Repository\CategoryRepository;
use App\Repository\StyleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-artworks',
    description: 'Importe des oeuvres depuis l\'API Art Institute of Chicago (ETL).',
)]
class ImportArtworksCommand
{
    private const API_URL = 'https://api.artic.edu/api/v1/artworks/search';
    private const FIELDS = 'id,title,artist_title,date_display,image_id,style_titles,classification_titles';

    /** Mémoire de l'import : évite de créer deux fois la même entité avant le flush. */
    private array $artists = [];
    private array $styles = [];
    private array $categories = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $em,
        private readonly ArtworkRepository $artworkRepository,
        private readonly ArtistRepository $artistRepository,
        private readonly StyleRepository $styleRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Sujet de recherche (ex : monet, cat, water)')] string $sujet,
    ): int {
        // ===================== E - EXTRAIRE =====================
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => ['q' => $sujet, 'limit' => 50, 'fields' => self::FIELDS],
        ]);
        $rows = $response->toArray()['data'] ?? [];
        $io->info(sprintf('%d oeuvres reçues pour « %s ».', \count($rows), $sujet));

        $imported = $ignoredInvalid = $ignoredDuplicate = 0;
        $seenIds = [];

        foreach ($rows as $row) {
            // ===================== T - TRANSFORMER =====================

            // T-règle 1 : sans titre, sans auteur ou sans image_id -> on ignore
            $title = trim((string) ($row['title'] ?? ''));
            $artistName = trim((string) ($row['artist_title'] ?? ''));
            $imageId = trim((string) ($row['image_id'] ?? ''));
            if ('' === $title || '' === $artistName || '' === $imageId) {
                ++$ignoredInvalid;
                continue;
            }

            // T-règle 2 : clé naturelle (id source) déjà en base -> on ignore
            $aicId = (int) $row['id'];
            if (isset($seenIds[$aicId]) || null !== $this->artworkRepository->findOneBy(['aicId' => $aicId])) {
                ++$ignoredDuplicate;
                continue;
            }
            $seenIds[$aicId] = true;

            // T-mapping (T-règle 6 : titre tronqué à 255 caractères)
            $artwork = (new Artwork())
                ->setAicId($aicId)
                ->setTitle(mb_substr($title, 0, 255))
                ->setDate(isset($row['date_display']) ? mb_substr((string) $row['date_display'], 0, 30) : null)
                ->setImageId($imageId)
                ->setArtist($this->findOrCreateArtist($artistName)); // T-règle 3

            // T-règle 4 : styles normalisés
            foreach ($this->normalize($row['style_titles'] ?? []) as $name) {
                $artwork->addStyle($this->findOrCreateStyle($name));
            }

            // T-règle 5 : classifications normalisées
            foreach ($this->normalize($row['classification_titles'] ?? []) as $name) {
                $artwork->addCategory($this->findOrCreateCategory($name));
            }

            // ===================== L - CHARGER =====================
            $this->em->persist($artwork);
            ++$imported;
        }

        // L - CHARGER : un seul flush à la fin
        $this->em->flush();

        $io->success(sprintf(
            '%d importée(s), %d ignorée(s) car incomplète(s), %d ignorée(s) car déjà en base.',
            $imported, $ignoredInvalid, $ignoredDuplicate,
        ));

        return Command::SUCCESS;
    }

    /** T-règle 3 : artiste retrouvé par son nom, sinon créé. */
    private function findOrCreateArtist(string $name): Artist
    {
        return $this->artists[$name] ??= $this->artistRepository->findOneBy(['name' => $name])
            ?? $this->persistNew((new Artist())->setName(mb_substr($name, 0, 255)));
    }

    /** T-règle 4 : style réutilisé s'il existe, sinon créé. */
    private function findOrCreateStyle(string $name): Style
    {
        return $this->styles[$name] ??= $this->styleRepository->findOneBy(['name' => $name])
            ?? $this->persistNew((new Style())->setName($name));
    }

    /** T-règle 5 : catégorie réutilisée si elle existe, sinon créée. */
    private function findOrCreateCategory(string $name): Category
    {
        return $this->categories[$name] ??= $this->categoryRepository->findOneBy(['name' => $name])
            ?? $this->persistNew((new Category())->setName($name));
    }

    /**
     * T-règles 4 et 5 : minuscules, sans espaces autour, sans vide ni doublon.
     *
     * @return list<string>
     */
    private function normalize(array $values): array
    {
        $names = array_map(static fn ($v) => mb_substr(mb_strtolower(trim((string) $v)), 0, 100), $values);

        return array_values(array_unique(array_filter($names, static fn ($n) => '' !== $n)));
    }

    private function persistNew(object $entity): object
    {
        $this->em->persist($entity);

        return $entity;
    }
}
