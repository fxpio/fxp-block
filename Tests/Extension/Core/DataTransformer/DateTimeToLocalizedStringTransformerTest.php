<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core\DataTransformer;

use Fxp\Component\Block\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DateTimeToLocalizedStringTransformerTest extends TestCase
{
    protected $dateTime;
    protected $dateTimeWithoutSeconds;

    protected function setUp()
    {
        parent::setUp();

        // Since we test against "fr_FR", we need the full implementation
        IntlTestHelper::requireFullIntl($this, false);

        \Locale::setDefault('fr_FR');

        $this->dateTime = new \DateTime('2013-10-11 05:05:05 UTC');
        $this->dateTimeWithoutSeconds = new \DateTime('2013-10-11 05:05:00 UTC');
    }

    protected function tearDown()
    {
        $this->dateTime = null;
        $this->dateTimeWithoutSeconds = null;
    }

    public static function assertDateTimeEquals(\DateTime $expected, \DateTime $actual)
    {
        self::assertEquals($expected->format('c'), $actual->format('c'));
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        if ($expected instanceof \DateTime && $actual instanceof \DateTime) {
            $expected = $expected->format('c');
            $actual = $actual->format('c');
        }

        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public function dataProvider()
    {
        return [
            [\IntlDateFormatter::SHORT, null, ['11/10/2013 05:05', '11/10/13 05:05'], '2013-10-11 05:05:00 UTC'],
            [\IntlDateFormatter::MEDIUM, null, ['11 oct. 2013 à 05:05', '11 oct. 2013 05:05'], '2013-10-11 05:05:00 UTC'],
            [\IntlDateFormatter::LONG, null, ['11 octobre 2013 à 05:05', '11 octobre 2013 05:05'], '2013-10-11 05:05:00 UTC'],
            [\IntlDateFormatter::FULL, null, ['vendredi 11 octobre 2013 à 05:05', 'vendredi 11 octobre 2013 05:05'], '2013-10-11 05:05:00 UTC'],
            [\IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, ['23/05/2012', '23/05/12'], '2012-05-23 00:00:00 UTC'],
            [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, '23 mai 2012', '2012-05-23 00:00:00 UTC'],
            [\IntlDateFormatter::LONG, \IntlDateFormatter::NONE, '23 mai 2012', '2012-05-23 00:00:00 UTC'],
            [\IntlDateFormatter::FULL, \IntlDateFormatter::NONE, 'mercredi 23 mai 2012', '2012-05-23 00:00:00 UTC'],
            [null, \IntlDateFormatter::SHORT, ['11/10/2013 05:05', '11/10/13 05:05'], '2013-10-11 05:05:00 UTC'],
            [null, \IntlDateFormatter::MEDIUM, ['23/05/2012 05:05:23', '23/05/12 05:05:23'], '2012-05-23 05:05:23 UTC'],
            [null, \IntlDateFormatter::LONG, ['23/05/2012 05:05:23 UTC', '23/05/12 05:05:23 UTC'], '2012-05-23 05:05:23 UTC'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::SHORT, '04:05', '1970-01-01 04:05:00 UTC'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::MEDIUM, '04:05:06', '1970-01-01 04:05:06 UTC'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::LONG, '04:05:06 UTC', '1970-01-01 04:05:06 UTC'],
            [null, null, ['11/10/2013 05:05', '11/10/13 05:05'], '2013-10-11 05:05:00 UTC'],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTransform($dateFormat, $timeFormat, $output, $input)
    {
        $transformer = new DateTimeToLocalizedStringTransformer(
            \IntlDateFormatter::GREGORIAN,
            $dateFormat,
            $timeFormat,
            'UTC',
            \Locale::getDefault()
        );

        $input = new \DateTime($input);

        if (is_array($output)) {
            $this->assertContains($transformer->transform($input), $output);
        } else {
            $this->assertEquals($output, $transformer->transform($input));
        }
    }

    public function testTransformFullTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(null, null, \IntlDateFormatter::FULL, 'Europe/Paris');

        $this->assertContains($transformer->transform($this->dateTime), [
            '11/10/2013 07:05:05 heure d’été d’Europe centrale',
            '11/10/2013 07:05:05 heure avancée d’Europe centrale',
            '11/10/13 07:05:05 heure avancée d’Europe centrale',
        ]);
    }

    public function testTransformToDifferentLocale()
    {
        \Locale::setDefault('en_US');

        $transformer = new DateTimeToLocalizedStringTransformer(null, null, null, 'UTC');

        $this->assertContains($transformer->transform($this->dateTime), [
            '10/11/13, 5:05 AM',
            '10/11/13 5:05 AM',
        ]);
    }

    public function testTransformEmpty()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformWithDifferentTimezones()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(null, null, null, 'Asia/Hong_Kong');

        $input = new \DateTime('2012-05-23 05:05:23 America/New_York');

        $dateTime = clone $input;
        $dateTime->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertContains($transformer->transform($input), [
            $dateTime->format('d/m/Y H:i'),
            $dateTime->format('d/m/y H:i'),
        ]);
    }

    public function testTransformWithDifferentPatterns()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(\IntlDateFormatter::GREGORIAN, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC');

        $this->assertContains($transformer->transform($this->dateTime), [
            'vendredi 11 octobre 2013 à 05:05:05 UTC',
            'vendredi 11 octobre 2013 05:05:05 UTC',
        ]);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformRequiresValidDateTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();
        $transformer->transform('2013-10-11');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testValidateDateFormatOption()
    {
        new DateTimeToLocalizedStringTransformer(null, 'foobar');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testValidateTimeFormatOption()
    {
        new DateTimeToLocalizedStringTransformer(null, null, 'foobar');
    }
}
