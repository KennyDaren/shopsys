<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Stock\Stock;
use App\Model\Store\Exception\StoreDomainNotFoundException;
use App\Model\Store\OpeningHours\Exception\OpeningHoursNotFoundException;
use App\Model\Store\OpeningHours\OpeningHours;
use App\Model\Store\OpeningHours\OpeningHoursData;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Country\Country;

/**
 * @ORM\Table(name="stores")
 * @ORM\Entity
 */
class Store implements OrderableEntityInterface
{
    private const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    /**
     * @var \App\Model\Store\StoreDomain[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="App\Model\Store\StoreDomain", mappedBy="store", cascade={"persist"})
     */
    protected Collection $domains;

    /**
     * @var \App\Model\Stock\Stock|null
     * @ORM\ManyToOne(targetEntity="App\Model\Stock\Stock", inversedBy="stores", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected ?Stock $stock;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isDefault;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected string $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected ?string $externalId;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected string $street;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected string $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=30)
     */
    protected string $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Country $country;

    /**
     * @var \Doctrine\Common\Collections\Collection<int,\App\Model\Store\OpeningHours\OpeningHours>
     * @ORM\OneToMany(targetEntity="\App\Model\Store\OpeningHours\OpeningHours", mappedBy="store", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"dayOfWeek" = "ASC"})
     */
    protected Collection $openingHours;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $contactInfo;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $specialMessage;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $locationLatitude;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $locationLongitude;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected int $position;

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function __construct(StoreData $storeData)
    {
        $this->domains = new ArrayCollection();
        $this->position = self::GEDMO_SORTABLE_LAST_POSITION;
        $this->createDomains($storeData);
        $this->uuid = $storeData->uuid ?: Uuid::uuid4()->toString();
        $this->openingHours = new ArrayCollection();
        $this->setData($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function edit(StoreData $storeData)
    {
        $this->setDomains($storeData);
        $this->setData($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    public function setData(StoreData $storeData): void
    {
        $this->isDefault = $storeData->isDefault;
        $this->name = $storeData->name;
        $this->stock = $storeData->stock;
        $this->description = $storeData->description;
        $this->externalId = $storeData->externalId;
        $this->street = $storeData->street;
        $this->city = $storeData->city;
        $this->postcode = $storeData->postcode;
        $this->country = $storeData->country;
        $this->openingHours = $this->createOpeningHours($storeData->openingHours);
        $this->contactInfo = $storeData->contactInfo;
        $this->specialMessage = $storeData->specialMessage;
        $this->locationLatitude = $storeData->locationLatitude;
        $this->locationLongitude = $storeData->locationLongitude;
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    protected function createDomains(StoreData $storeData): void
    {
        $domainIds = array_keys($storeData->isEnabledOnDomains);

        foreach ($domainIds as $domainId) {
            $storeDomain = new StoreDomain($this, $domainId);
            $this->domains->add($storeDomain);
        }

        $this->setDomains($storeData);
    }

    /**
     * @param \App\Model\Store\StoreData $storeData
     */
    protected function setDomains(StoreData $storeData): void
    {
        foreach ($this->domains as $storeDomain) {
            $domainId = $storeDomain->getDomainId();
            $storeDomain->setEnabled($storeData->isEnabledOnDomains[$domainId]);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \App\Model\Stock\Stock|null
     */
    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @return \App\Model\Store\OpeningHours\OpeningHours[]
     */
    public function getOpeningHours(): array
    {
        return $this->openingHours->getValues();
    }

    /**
     * @param \DateTimeZone $dateTimeZone
     * @return \App\Model\Store\OpeningHours\OpeningHours
     */
    public function getTodayOpeningHours(DateTimeZone $dateTimeZone): OpeningHours
    {
        $dayOfWeek = (int)(new DateTimeImmutable('now', $dateTimeZone))->format('N');

        foreach ($this->openingHours as $openingHours) {
            if ($openingHours->getDayOfWeek() === $dayOfWeek) {
                return $openingHours;
            }
        }

        throw new OpeningHoursNotFoundException($this->id, $dayOfWeek);
    }

    /**
     * @param \DateTimeImmutable $date
     * @param \App\Model\Store\ClosedDay\ClosedDay[] $closedDays
     * @return bool
     */
    public function isOpen(DateTimeImmutable $date, array $closedDays): bool
    {
        $todayOpeningHours = $this->getTodayOpeningHours($date->getTimezone());

        if (array_key_exists($date->format('N'), $closedDays)) {
            return false;
        }

        $firstOpeningTime = $this->getTimeWithTimeZone($todayOpeningHours->getFirstOpeningTime(), $date->getTimezone());
        $firstClosingTime = $this->getTimeWithTimeZone($todayOpeningHours->getFirstClosingTime(), $date->getTimezone());
        $secondOpeningTime = $this->getTimeWithTimeZone($todayOpeningHours->getSecondOpeningTime(), $date->getTimezone());
        $secondClosingTime = $this->getTimeWithTimeZone($todayOpeningHours->getSecondClosingTime(), $date->getTimezone());

        $hasFirstTimeSet = $firstOpeningTime !== null && $firstClosingTime !== null;
        $hasSecondTimeSet = $secondOpeningTime !== null && $secondClosingTime !== null;

        $isFirstTimeOpen = $hasFirstTimeSet && $date >= $firstOpeningTime && $date < $firstClosingTime;
        $isSecondTimeOpen = $hasSecondTimeSet && $date >= $secondOpeningTime && $date < $secondClosingTime;

        return $isFirstTimeOpen || $isSecondTimeOpen;
    }

    /**
     * @param string|null $time
     * @param \DateTimeZone $dateTimeZone
     * @return \DateTimeImmutable|null
     */
    protected function getTimeWithTimeZone(?string $time, DateTimeZone $dateTimeZone): ?DateTimeImmutable
    {
        if ($time === null) {
            return null;
        }

        return DateTimeImmutable::createFromFormat(
            'H:i',
            $time,
            $dateTimeZone,
        );
    }

    /**
     * @return string|null
     */
    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }

    /**
     * @return string|null
     */
    public function getSpecialMessage(): ?string
    {
        return $this->specialMessage;
    }

    /**
     * @return string|null
     */
    public function getLocationLatitude(): ?string
    {
        return $this->locationLatitude;
    }

    /**
     * @return string|null
     */
    public function getLocationLongitude(): ?string
    {
        return $this->locationLongitude;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabled(int $domainId): bool
    {
        return $this->getStoreDomain($domainId)->isEnabled();
    }

    /**
     * @return \App\Model\Store\StoreDomain[]
     */
    public function getEnabledDomains(): array
    {
        return array_filter($this->domains->getValues(), static fn (StoreDomain $storeDomain) => $storeDomain->isEnabled());
    }

    /**
     * @param int $domainId
     * @return \App\Model\Store\StoreDomain
     */
    protected function getStoreDomain(int $domainId): StoreDomain
    {
        foreach ($this->domains as $storeDomain) {
            if ($storeDomain->getDomainId() === $domainId) {
                return $storeDomain;
            }
        }

        throw new StoreDomainNotFoundException($domainId, $this->id);
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function setDefault(): void
    {
        $this->isDefault = true;
    }

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHoursData[] $openingHours
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function createOpeningHours(array $openingHours): ArrayCollection
    {
        $openingHours = array_map(function (OpeningHoursData $openingHourData): OpeningHours {
            $openingHours = new OpeningHours($openingHourData);
            $openingHours->setStore($this);

            return $openingHours;
        }, $openingHours);

        return new ArrayCollection($openingHours);
    }
}
