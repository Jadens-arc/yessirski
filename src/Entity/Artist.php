<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ArtistTrack>
     */
    #[ORM\OneToMany(targetEntity: ArtistTrack::class, mappedBy: 'artist')]
    private Collection $artist_tracks;

    public function __construct()
    {
        $this->artist_tracks = new ArrayCollection();
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
     * @return Collection<int, ArtistTrack>
     */
    public function getArtistTracks(): Collection
    {
        return $this->artist_tracks;
    }

    public function addArtistTrack(ArtistTrack $artistTrack): static
    {
        if (!$this->artist_tracks->contains($artistTrack)) {
            $this->artist_tracks->add($artistTrack);
            $artistTrack->setArtist($this);
        }

        return $this;
    }

    public function removeArtistTrack(ArtistTrack $artistTrack): static
    {
        if ($this->artist_tracks->removeElement($artistTrack)) {
            // set the owning side to null (unless already changed)
            if ($artistTrack->getArtist() === $this) {
                $artistTrack->setArtist(null);
            }
        }

        return $this;
    }

    public function getTracks(): Collection
    {
        $tracks = new ArrayCollection();
        foreach ($this->artist_tracks as $artist_track) {
            $tracks->add($artist_track->getTrack());
        }
        return $tracks;
    }
}
