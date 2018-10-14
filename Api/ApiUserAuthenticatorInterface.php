<?php

namespace Olveneer\DataProcessorBundle\Api;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface ApiUserAuthenticatorInterface
 * @package App\Service\API
 * @author Douwe
 *
 * De taak van de User Authenticator is om vanuit data uit de json een gebruiker in te kunnen loggen.
 */
interface ApiUserAuthenticatorInterface
{
    /**
     * @param $json
     * @return bool
     *
     * Returnt false wanneer er vereiste velden missen in de json, en true wanneer alles klopt.
     */
    public function isDataPresent($json): bool;

    /**
     * @return string
     *
     * Als de isDatPresent false returnt krijgt de gebruiker dit bericht te zien.
     */
    public function getMissingDataMessage(): string;

    /**
     * @return string
     *
     * Als de authenticate function geen valide user returnt krijgt de gebruiker dit bericht te zien.
     */
    public function getInvalidDataMessage(): string;

    /**
     * @param $json
     * @return UserInterface
     *
     * Returnt een user vanuit de json of null als de gebruiker niet gevonden kan worden vanuit de json.
     */
    public function authenticate($json): ?UserInterface;
}
