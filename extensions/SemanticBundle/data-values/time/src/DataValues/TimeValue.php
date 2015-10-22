<?php

namespace DataValues;

/**
 * Class representing a time value.
 * @see https://meta.wikimedia.org/wiki/Wikidata/Data_model#Dates_and_times
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TimeValue extends DataValueObject {

	const PRECISION_Ga = 0; // Gigayear
	const PRECISION_100Ma = 1; // 100 Megayears
	const PRECISION_10Ma = 2; // 10 Megayears
	const PRECISION_Ma = 3; // Megayear
	const PRECISION_100ka = 4; // 100 Kiloyears
	const PRECISION_10ka = 5; // 10 Kiloyears
	const PRECISION_ka = 6; // Kiloyear
	const PRECISION_100a = 7; // 100 years
	const PRECISION_10a = 8; // 10 years
	const PRECISION_YEAR = 9;
	const PRECISION_MONTH = 10;
	const PRECISION_DAY = 11;
	const PRECISION_HOUR = 12;
	const PRECISION_MINUTE = 13;
	const PRECISION_SECOND = 14;

	/**
	 * Point in time, represented per ISO8601.
	 * The year always having 11 digits, the date always be signed, in the format +00000002013-01-01T00:00:00Z
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	protected $time;

	/**
	 * Unit used for the $after and $before values.
	 *
	 * @since 0.1
	 *
	 * @var integer
	 */
	protected $precision;

	/**
	 * If the date is uncertain, how many units after the given time could it be?
	 * The unit is given by the precision.
	 *
	 * @since 0.1
	 *
	 * @var integer
	 */
	protected $after;

	/**
	 * If the date is uncertain, how many units before the given time could it be?
	 * The unit is given by the precision.
	 *
	 * @since 0.1
	 *
	 * @var integer
	 */
	protected $before;

	/**
	 * Timezone information as an offset from UTC in minutes.
	 *
	 * @since 0.1
	 *
	 * @var int
	 */
	protected $timezone;

	/**
	 * URI identifying the calendar model that should be used to display this time value.
	 * Note that time is always saved in proleptic Gregorian, this URI states how the value should be displayed.
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	protected $calendarModel;

	/**
	 * @since 0.1
	 *
	 * @param string $time
	 * @param integer $timezone
	 * @param integer $before
	 * @param integer $after
	 * @param integer $precision
	 * @param string $calendarModel
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( $time, $timezone, $before, $after, $precision, $calendarModel ) {
		if ( !is_string( $time ) ) {
			throw new IllegalValueException( '$time needs to be a string' );
		}

		if ( !preg_match( '!^[-+]\d{1,16}-(0\d|1[012])-([012]\d|3[01])T([01]\d|2[0123]):[0-5]\d:([0-5]\d|6[012])Z$!', $time ) ) {
			throw new IllegalValueException( '$time needs to be a valid ISO 8601 date, given ' . $time );
		}

		if ( !is_integer( $timezone ) ) {
			throw new IllegalValueException( '$timezone needs to be an integer' );
		}

		if ( $timezone < -12 * 3600 || $timezone > 14 * 3600 ) {
			throw new IllegalValueException( '$timezone out of allowed bounds' );
		}

		if ( !is_integer( $before ) || $before < 0 ) {
			throw new IllegalValueException( '$before needs to be an unsigned integer' );
		}

		if ( !is_integer( $after ) || $after < 0 ) {
			throw new IllegalValueException( '$after needs to be an unsigned integer' );
		}

		if ( !is_integer( $precision ) ) {
			throw new IllegalValueException( '$precision needs to be an integer' );
		}

		if ( $precision < self::PRECISION_Ga || $precision > self::PRECISION_SECOND ) {
			throw new IllegalValueException( '$precision out of allowed bounds' );
		}

		if ( !is_string( $calendarModel ) ) { //XXX: enforce IRI? Or at least a size limit?
			throw new IllegalValueException( '$calendarModel needs to be a string' );
		}

		// Can haz scalar type hints plox? ^^

		$this->time = $time;
		$this->timezone = $timezone;
		$this->before = $before;
		$this->after = $after;
		$this->precision = $precision;
		$this->calendarModel = $calendarModel;
	}

	/**
	 * @see $time
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @see $calendarModel
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getCalendarModel() {
		return $this->calendarModel;
	}

	/**
	 * @see $before
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getBefore() {
		return $this->before;
	}

	/**
	 * @see $after
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getAfter() {
		return $this->after;
	}

	/**
	 * @see $precision
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getPrecision() {
		return $this->precision;
	}

	/**
	 * @see $timezone
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * @see DataValue::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public static function getType() {
		return 'time';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @since 0.1
	 *
	 * @return string|float|int
	 */
	public function getSortKey() {
		return $this->time;
	}

	/**
	 * Returns the text.
	 * @see DataValue::getValue
	 *
	 * @since 0.1
	 *
	 * @return TimeValue
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function serialize() {
		return json_encode( array_values( $this->getArrayValue() ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 *
	 * @return MonolingualTextValue
	 * @throws IllegalValueException
	 */
	public function unserialize( $value ) {
		list( $time, $timezone, $before, $after, $precision, $calendarModel ) = json_decode( $value );
		$this->__construct( $time, $timezone, $before, $after, $precision, $calendarModel );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @since 0.1
	 *
	 * @return mixed
	 */
	public function getArrayValue() {
		return array(
			'time' => $this->time,
			'timezone' => $this->timezone,
			'before' => $this->before,
			'after' => $this->after,
			'precision' => $this->precision,
			'calendarmodel' => $this->calendarModel,
		);
	}

	/**
	 * Constructs a new instance of the DataValue from the provided data.
	 * This can round-trip with @see getArrayValue
	 *
	 * @since 0.1
	 *
	 * @param mixed $data
	 *
	 * @return TimeValue
	 * @throws IllegalValueException
	 */
	public static function newFromArray( $data ) {
		self::requireArrayFields( $data, array( 'time', 'timezone', 'before', 'after', 'precision', 'calendarmodel' ) );

		return new static(
			$data['time'],
			$data['timezone'],
			$data['before'],
			$data['after'],
			$data['precision'],
			$data['calendarmodel']
		);
	}

}
