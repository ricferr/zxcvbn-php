<?php

namespace ZxcvbnPhp\Test\Matchers;

use ZxcvbnPhp\Matchers\DateMatch;

class DateTest extends AbstractMatchTest
{
    public function testSeparators()
    {
        $separators = array(
            '',
            ' ',
            '-',
            '/',
            '\\',
            '_',
            '.'
        );
        foreach ($separators as $sep) {
            $password = "13{$sep}2{$sep}1921";
            $this->checkMatches(
                "matches dates that use '$sep' as a separator",
                DateMatch::match($password),
                'date',
                [ $password ],
                [[ 0, strlen($password) - 1 ]],
                [
                    'separator' => [$sep],
                    'year'      => [1921],
                    'month'     => [2],
                    'day'       => [13],
                ]
            );
        }
    }

    public function testDateOrders()
    {
        list($d, $m, $y) = array(8, 8, 88);
        $orders = array('mdy', 'dmy', 'ymd', 'ydm');
        foreach ($orders as $order) {
            $password = str_replace(
                ['y', 'm', 'd'],
                [$y, $m, $d],
                $order
            );
            $this->checkMatches(
                "matches dates with $order format",
                DateMatch::match($password),
                'date',
                [ $password ],
                [[ 0, strlen($password) - 1 ]],
                [
                    'separator' => [''],
                    'year'      => [1988],
                    'month'     => [8],
                    'day'       => [8],
                ]
            );
        }
    }

    public function testMatchesClosestToReferenceYear()
    {
        $password = '111504';
        $this->checkMatches(
            "matches the date with year closest to REFERENCE_YEAR when ambiguous",
            DateMatch::match($password),
            'date',
            [ $password ],
            [[ 0, strlen($password) - 1 ]],
            [
                'separator' => [''],
                'year'      => [2004], // picks '04' -> 2004 as year, not '1504'
                'month'     => [11],
                'day'       => [15],
            ]
        );
    }

    public function testMatch()
    {
        $dates = array(
            array(1,  1,  1999),
            array(11, 8,  2000),
            array(9,  12, 2005),
            array(22, 11, 1551),
        );

        foreach ($dates as list($day, $month, $year)) {
            $password = "{$year}{$month}{$day}";
            $this->checkMatches(
                "matches $password",
                DateMatch::match($password),
                'date',
                [ $password ],
                [[ 0, strlen($password) - 1 ]],
                [
                    'separator' => [''],
                    'year'      => [$year],
                ]
            );

            $password = "{$year}.{$month}.{$day}";
            $this->checkMatches(
                "matches $password",
                DateMatch::match($password),
                'date',
                [ $password ],
                [[ 0, strlen($password) - 1 ]],
                [
                    'separator' => ['.'],
                    'year'      => [$year],
                ]
            );
        }
    }

    public function testMatchesZeroPaddedDates()
    {
        $password = "02/02/02";
        $this->checkMatches(
            "matches zero-padded dates",
            DateMatch::match($password),
            'date',
            [ $password ],
            [[ 0, strlen($password) - 1 ]],
            [
                'separator' => ['/'],
                'year'      => [2002],
                'month'     => [2],
                'day'       => [2],
            ]
        );
    }

    public function testMatchesEmbeddedDates()
    {
        $prefixes = array('a', 'ab');
        $suffixes = array('!');
        $pattern = '1/1/91';

        foreach ($this->generatePasswords($pattern, $prefixes, $suffixes) as list($password, $i, $j)) {
            $this->checkMatches(
                "matches embedded dates",
                DateMatch::match($password),
                'repeat',
                [$pattern],
                [[$i, $j]],
                [
                    'year'  => 1991,
                    'month' => 1,
                    'day'   => 1
                ]
            );
        }
    }

    public function testMatchesOverlappingDates()
    {
        $password = "12/20/1991.12.20";
        $this->checkMatches(
            "matches overlapping dates",
            DateMatch::match($password),
            'date',
            [ '12/20/1991', '1991.12.20' ],
            [[ 0, 9 ], [ 6, 15 ]],
            [
                'separator' => ['/', '.'],
                'year'      => [1991, 1991],
                'month'     => [12, 12],
                'day'       => [20, 20],
            ]
        );
    }

    public function testMatchesDatesPadded()
    {
        $password = "912/20/919";
        $this->checkMatches(
            "matches dates padded by non-ambiguous digits",
            DateMatch::match($password),
            'date',
            [ '12/20/91' ],
            [[ 1, 8 ]],
            [
                'separator' => ['/'],
                'year'      => [1991],
                'month'     => [12],
                'day'       => [20],
            ]
        );
    }
}