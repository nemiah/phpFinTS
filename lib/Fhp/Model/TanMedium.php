<?php /** @noinspection PhpUnused */

namespace Fhp\Model;

/**
 * For two-step authentication, users need to enter a TAN, which can be obtained in various ways. After choosing one of
 * these ways, i.e. choosing a a {@link TanMode} (SMS, TAN generator device, and so on), the user might have to choose
 * which of their TAN media they want to use within this mode, in case they have multiple. For instance, a user might
 * have multiple mobile phone numbers configured for smsTAN, might have multiple TAN generators, or multiple iTAN lists.
 * Each {@link TanMedium} instance describes one of these options.
 */
interface TanMedium
{
    /**
     * @return string A user-readable name for this TAN medium, which serves as its identifier at the same time. This is
     *     what the application needs to persist when it wants to remember the users decision for future transactions.
     */
    public function getName();

    /**
     * @return string|null In case this is a mobileTAN/smsTAN medium, this is its (possibly obfuscated) phone number.
     */
    public function getPhoneNumber();

    // TODO Consider making more information from TanMediumListeV4 available here.
}
