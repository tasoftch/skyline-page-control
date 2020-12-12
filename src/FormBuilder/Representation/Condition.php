<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use TASoft\Util\ValueObject\DateTime;

class Condition
{
	const ENABLE_FROM_OPTION = 1<<0;
	const ENABLE_UNTIL_OPTION = 1<<1;
	const ENABLE_HOSTS_OPTION = 1<<2;

	const VALUE_DID_CHANGE_OPTION = 1<<7;

	/** @var DateTime|null */
	private $date_from;
	/** @var DateTime|null */
	private $date_until;
	/** @var string[] */
	private $ip_addresses = [];

	/** @var int  */
	private $options = 0;

	/**
	 * Condition constructor.
	 * @param DateTime|null $date_from
	 * @param DateTime|null $date_until
	 * @param string[] $ip_addresses
	 */
	public function __construct(DateTime $date_from = NULL, DateTime $date_until = NULL, $options = 0, array $ip_addresses = [])
	{
		$this->date_from = $date_from;
		$this->date_until = $date_until;
		$this->ip_addresses = $ip_addresses;
		$this->options = $options;
	}


	/**
	 * @return DateTime|null
	 */
	public function getDateFrom(): ?DateTime
	{
		return $this->date_from;
	}

	/**
	 * @return DateTime|null
	 */
	public function getDateUntil(): ?DateTime
	{
		return $this->date_until;
	}

	/**
	 * @return string[]
	 */
	public function getIpAddresses(): array
	{
		return $this->ip_addresses;
	}

	/**
	 * @param DateTime|null $date_from
	 * @return static
	 */
	public function setDateFrom(?DateTime $date_from)
	{
		$this->date_from = $date_from;
		return $this;
	}

	/**
	 * @param DateTime|null $date_until
	 * @return static
	 */
	public function setDateUntil(?DateTime $date_until)
	{
		$this->date_until = $date_until;
		return $this;
	}

	/**
	 * @param string $addr
	 * @return static
	 */
	public function addIpAddress(string $addr) {
		if(!in_array($addr, $this->ip_addresses))
			$this->ip_addresses[] = $addr;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOptions(): int
	{
		return $this->options;
	}

	/**
	 * @param int $options
	 * @return static
	 */
	public function setOptions(int $options)
	{
		$this->options = $options;
		return $this;
	}

	public function __toString() {
		return sprintf("%s|%s|%d|%s",
			$this->date_from?($this->date_from->format("Y-m-d G:i:s")):'',
			$this->date_until?($this->date_until->format("Y-m-d G:i:s")):'',
			$this->getOptions(),
			implode("|", $this->getIpAddresses())
		);
	}
}