<?php

namespace Olveneer\TwigComponentsBundle\Tests;


use Olveneer\DataProcessorBundle\Api\ApiUserAuthenticatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TestAuthenticator implements ApiUserAuthenticatorInterface
{

    /**
     * @param $json
     * @return bool
     *
     * Returnt false wanneer er vereiste velden missen in de json, en true wanneer alles klopt.
     */
    public function isDataPresent($json): bool
    {
        return isset($json['api-key']);
    }

    /**
     * @return string
     *
     * Als de isDatPresent false returnt krijgt de gebruiker dit bericht te zien.
     */
    public function getMissingDataMessage(): string
    {
        return 'DATA IS MISSING';
    }

    /**
     * @return string
     *
     * Als de authenticate function geen valide user returnt krijgt de gebruiker dit bericht te zien.
     */
    public function getInvalidDataMessage(): string
    {
        return 'DATA IS INVALID';
    }

    /**
     * @param $json
     * @return UserInterface
     *
     * Returnt een user vanuit de json of null als de gebruiker niet gevonden kan worden vanuit de json.
     */
    public function authenticate($json): ?UserInterface
    {
        $key = $json['api-key'];


    }
}