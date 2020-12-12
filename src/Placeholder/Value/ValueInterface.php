<?php
namespace Skyline\PageControl\Placeholder\Value;

interface ValueInterface
{
	/**
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Serialized representation of a value
	 *
	 * @return string
	 */
	public function toScalar(): string;

	/**
	 * ValueInterface constructor.
	 * @param string $scalarRepresentation
	 */
	public function __construct(string $scalarRepresentation);
}