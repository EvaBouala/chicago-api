<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Repository\ArtworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtworkRepository::class)]
#[ApiResource (
    operations: [
        // Filtres de la liste : recherche partielle, sans tenir compte de la casse
        new GetCollection(parameters: [
            'titre' => new QueryParameter(filter: new PartialSearchFilter(), property: 'title'),
            'artiste' => new QueryParameter(filter: new PartialSearchFilter(), property: 'artist.name'),
            'style' => new QueryParameter(filter: new PartialSearchFilter(), property: 'styles.name'),
            'categorie' => new QueryParameter(filter: new PartialSearchFilter(), property: 'categories.name'),
            'tri' => new QueryParameter(filter: new SortFilter(), property: 'title'),
        ]),
        new Get(), new Post(), new Patch(), new Delete(),
    ],
    // Pagination : 10 oeuvres par page, ?page=2 pour la suite
    paginationItemsPerPage: 10,
    normalizationContext: ['groups' => ['artwork:lire']],
    denormalizationContext: ['groups' => ['artwork:ecrire']],
)]
class Artwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(unique:true)]
    #[Groups(['artwork:lire'])]
    private ?int $id = null;

    /** Identifiant de l'oeuvre dans l'API Art Institute of Chicago (clé naturelle, règle T2).
     *  Aucun groupe : caché dans l'API, jamais écrit par le client, seulement par l'import. */
    #[ORM\Column(unique: true, nullable: true)]
    private ?int $aicId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    #[Assert\NotBlank(allowNull: true)] 
    #[Assert\Length(max: 30)]
    private ?string $date = null;

    #[ORM\ManyToOne(inversedBy: 'yes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    #[Assert\NotNull]
    private ?Artist $artist = null;

    /**
     * @var Collection<int, Style>
     */
    #[ORM\ManyToMany(targetEntity: Style::class, inversedBy: 'artworks')]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    private Collection $styles;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'artworks')]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    private Collection $categories;

    #[ORM\Column(length: 255, unique:true)]
    #[Groups(['artwork:lire','artwork:ecrire'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $image_id = null;

    public function __construct()
    {
        $this->styles = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAicId(): ?int
    {
        return $this->aicId;
    }

    public function setAicId(?int $aicId): static
    {
        $this->aicId = $aicId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return Collection<int, Style>
     */
    public function getStyles(): Collection
    {
        return $this->styles;
    }

    public function addStyle(Style $style): static
    {
        if (!$this->styles->contains($style)) {
            $this->styles->add($style);
        }

        return $this;
    }

    public function removeStyle(Style $style): static
    {
        $this->styles->removeElement($style);

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getImageId(): ?string
    {
        return $this->image_id;
    }

    public function setImageId(string $image_id): static
    {
        $this->image_id = $image_id;

        return $this;
    }
}
