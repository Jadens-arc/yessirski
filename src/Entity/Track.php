<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deactivation_date = null;

    /**
     * @var Collection<int, ArtistTrack>
     */
    #[ORM\OneToMany(targetEntity: ArtistTrack::class, mappedBy: 'track')]
    private Collection $artist_tracks;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'track')]
    private Collection $plays;

    public function __construct()
    {
        $this->uuid = Uuid::v1();
        $this->created = new \DateTime();
        $this->active = true;
        $this->artist_tracks = new ArrayCollection();
        $this->plays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getDeactivationDate(): ?\DateTimeInterface
    {
        return $this->deactivation_date;
    }

    public function setDeactivationDate(?\DateTimeInterface $deactivation_date): static
    {
        $this->deactivation_date = $deactivation_date;

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
            $artistTrack->setTrack($this);
        }

        return $this;
    }

    public function removeArtistTrack(ArtistTrack $artistTrack): static
    {
        if ($this->artist_tracks->removeElement($artistTrack)) {
            // set the owning side to null (unless already changed)
            if ($artistTrack->getTrack() === $this) {
                $artistTrack->setTrack(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Play>
     */
    public function getPlays(): Collection
    {
        return $this->plays;
    }

    public function addPlay(Play $play): static
    {
        if (!$this->plays->contains($play)) {
            $this->plays->add($play);
            $play->setTrack($this);
        }

        return $this;
    }

    public function removePlay(Play $play): static
    {
        if ($this->plays->removeElement($play)) {
            // set the owning side to null (unless already changed)
            if ($play->getTrack() === $this) {
                $play->setTrack(null);
            }
        }

        return $this;
    }
}
