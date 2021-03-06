<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  This file is part of eTraxis.
//
//  You should have received a copy of the GNU General Public License
//  along with eTraxis. If not, see <https://www.gnu.org/licenses/>.
//
//----------------------------------------------------------------------

namespace eTraxis\Security\Authenticator;

use eTraxis\Application\Dictionary\LdapServerType;
use Webinarium\PropertyTrait;

/**
 * LDAP URI.
 *
 * @property-read string $scheme     LDAP scheme ('null', 'ldap', 'ldaps').
 * @property-read string $host       LDAP host name.
 * @property-read int    $port       LDAP port number.
 * @property-read string $username   Binding user.
 * @property-read string $password   Binding password.
 * @property-read string $encryption LDAP encryption ('none', 'ssl', 'tls').
 * @property-read string $type       Server type (see the "LdapServerType" dictionary).
 */
class LdapUri
{
    use PropertyTrait;

    public const SCHEME_NULL  = 'null';
    public const SCHEME_LDAP  = 'ldap';
    public const SCHEME_LDAPS = 'ldaps';

    public const ENCRYPTION_NONE = 'none';
    public const ENCRYPTION_SSL  = 'ssl';
    public const ENCRYPTION_TLS  = 'tls';

    private static array $supported_schemes = [
        self::SCHEME_NULL,
        self::SCHEME_LDAP,
        self::SCHEME_LDAPS,
    ];

    private static array $supported_encryptions = [
        self::ENCRYPTION_NONE,
        self::ENCRYPTION_SSL,
        self::ENCRYPTION_TLS,
    ];

    private string  $scheme;
    private string  $host;
    private int     $port;
    private ?string $username;
    private ?string $password;
    private string  $encryption;
    private string  $type;

    /**
     * Parses specified URL.
     *
     * @param string $url URL to a LDAP server.
     */
    public function __construct(string $url)
    {
        $uri = parse_url($url);

        $this->scheme   = $uri['scheme'] ?? self::SCHEME_NULL;
        $this->host     = $uri['host']   ?? 'localhost';
        $this->port     = $uri['port']   ?? 389;
        $this->username = $uri['user']   ?? null;
        $this->password = $uri['pass']   ?? null;
        $this->type     = LdapServerType::FALLBACK;

        $pattern = sprintf('/type=(%s)/i', implode('|', LdapServerType::keys()));

        if (preg_match($pattern, $uri['query'] ?? null, $matches)) {
            $this->type = $matches[1];
        }

        $pattern = sprintf('/encryption=(%s)/i', implode('|', self::$supported_encryptions));

        if (preg_match($pattern, $uri['query'] ?? null, $matches)) {
            $this->encryption = $matches[1];
        }

        if (!in_array($this->scheme ?? null, self::$supported_schemes, true)) {
            $this->scheme = self::SCHEME_NULL;
        }

        if (!in_array($this->encryption ?? null, self::$supported_encryptions, true)) {
            $this->encryption = self::ENCRYPTION_NONE;
        }
    }
}
