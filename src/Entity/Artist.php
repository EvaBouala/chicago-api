<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Contraints as Assert;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
#[ApiResource (
    normalizationContext: ['groups' => ['artist:lire']],
    denormalizationContext: ['groups' => ['artist:ecrire']],
)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['artist:lire','artist:ecrire','artwork:lire'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Artwork>
     */
    #[ORM\OneToMany(targetEntity: Artwork::class, mappedBy: 'artist')]
    private Collection $yes;

    public function __construct()
    {
        $this->yes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Artwork>
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(Artwork $ye): static
    {
        if (!$this->yes->contains($ye)) {
            $this->yes->add($ye);
            $ye->setArtist($this);
        }

        return $this;
    }

    public function removeYe(Artwork $ye): static
    {
        if ($this->yes->removeElement($ye)) {
            // set the owning side to null (unless already changed)
            if ($ye->getArtist() === $this) {
                $ye->setArtist(null);
            }
        }

        return $this;
    }
}
