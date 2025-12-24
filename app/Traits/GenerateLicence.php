<?php

namespace App\Traits;

trait GenerateLicence
{

    protected function getLicence()
    {
        $firstFourDigit = rand(1000, 9999);
        $secondFourDigit = rand(1000, 9999);

        $year = now()->format('Y');
        $reverseYear = strrev((string) $year);

        return 'SPAT' . '-' . $reverseYear . '-' . $firstFourDigit . '-' . $secondFourDigit;
    }
}
