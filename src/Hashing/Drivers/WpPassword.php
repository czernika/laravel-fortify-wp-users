<?php
/**
 * 
 */

declare(strict_types=1);

namespace Czernika\FortifyWpUsers\Hashing\Drivers;

use Hautelook\Phpass\PasswordHash;

class WpPassword implements WpPasswordContract
{
    public function __construct(
        protected PasswordHash $wpHasher
    ) {
    }

    /**
     * Create a hash (encrypt) of a plain text password.
     *
     * For integration with other applications, this function can be overwritten to
     * instead use the other package password checking algorithm.
     *
     * @uses PasswordHash::HashPassword
     *
     * @param  string  $password  Plain text user password to hash
     * @return string The hash string of the password
     */
    public function make($password)
    {
        return $this->wpHasher->HashPassword(trim($password));
    }

    /**
     * Checks the plaintext password against the encrypted Password.
     *
     * Maintains compatibility between old version and the new cookie authentication
     * protocol using PHPass library. The $hash parameter is the encrypted password
     * and the function compares the plain text password when encrypted similarly
     * against the already encrypted password to see if they match.
     *
     * @uses PasswordHash::CheckPassword
     *
     * @param  string  $password  Plaintext user's password
     * @param  string  $hash  Hash of the user's password to check against.
     * @return bool False, if the $password does not match the hashed password
     */
    public function check($password, $hash)
    {
        // If the hash is still md5...
        if (strlen($hash) <= 32) {
            return $hash == md5($password);
        }

        // If the stored hash is longer than an MD5, presume the
        // new style phpass portable hash.
        return $this->wpHasher->CheckPassword($password, $hash);
    }
}
