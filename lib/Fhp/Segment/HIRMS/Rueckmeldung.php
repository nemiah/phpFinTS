<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIRMS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Rückmeldung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (under letter R)
 */
class Rueckmeldung extends BaseDeg
{
	/** @var integer See also the Rueckmeldungscode class/enum. */
	public $rueckmeldungscode;
	/**
	 * O: bei Verwendung im Segment HIRMS
	 * N: bei Verwendung im Segment HIRMG
	 * @var string|null Max length: 7
	 */
	public $bezugsdatenelement;
	/** @var string Max length: 80 */
	public $rueckmeldungstext;
	/** @var string[]|null @Max(10), max length each: 35 */
	public $rueckmeldungsparameter;
}
