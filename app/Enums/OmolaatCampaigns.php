<?php

namespace App\Enums;

/**
 * Enum for Omolaat Campaigns
 * Contains campaign information including client name, logo, and ID
 */
enum OmolaatCampaigns: string
{
    case SWISS_CORNER = '1747137247129x739776732273887500';
    case PHONE_ZONE = '1752761032323x917623443535833300';
    case SHIFT = '1747810598320x156968070750276060';
    case ALBAROO = '1747125603033x487769220566336450';
    case TREASURE_ISLAND = '1741552144578x318868455379435500';
    case ALHAWWAJ = '1740302649348x452668867481960450';
    case REBUNE = '1737878379261x256102095784858100';
    case TAAM_STORE = '1747220103671x304261627208851650';
    case RYEFAL = '1751192653341x643267328393214500';
    case SEASON = '1750857966663x836170535528801400';
    case ESEVEN_STORE = '1747330773675x870676325236314700';

    /**
     * Get the client name for the campaign
     */
    public function getClientName(): string
    {
        return match($this) {
            self::SWISS_CORNER => 'الركن السويسري',
            self::PHONE_ZONE => 'فون زون',
            self::SHIFT => 'شيفت',
            self::ALBAROO => 'البارو - Albaroo',
            self::TREASURE_ISLAND => 'تريجر ايلاند',
            self::ALHAWWAJ => 'الحواج للصناعات الغذائية',
            self::REBUNE => ' ريبون العالمية للتجارة',
            self::TAAM_STORE => 'متجر تام',
            self::RYEFAL => ' ريفال',
            self::SEASON => 'سيزون - SEASON',
            self::ESEVEN_STORE => 'Eseven Store',
        };
    }

    /**
     * Get the logo URL for the campaign
     */
    public function getLogoUrl(): string
    {
        return match($this) {
            self::SWISS_CORNER => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1747150990445x263420454336673380/1000001557.png',
            self::PHONE_ZONE => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1753079406218x103283454595106910/1919.png',
            self::SHIFT => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1757238246125x113765624141613840/Blue.png',
            self::ALBAROO => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1747151013888x483519193517202050/1000001559.png',
            self::TREASURE_ISLAND => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1741718402473x281212354534661860/%D8%AA%D8%B1%D9%8A%D8%AC%D8%B1%20%D8%A7%D9%8A%D9%84%D8%A7%D9%86%D8%AF.png',
            self::ALHAWWAJ => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1740553339171x801886748398842500/%D8%A7%D9%84%D8%AD%D9%88%D8%A7%D8%AC%202.jpg',
            self::REBUNE => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1738495790501x314259140923446100/rebune.png',
            self::TAAM_STORE => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1747239819422x984395956495354100/1000001587.png',
            self::RYEFAL => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1756797383129x117432522514897100/23%20%D8%B1%D9%8A%D9%81%D8%A7%D9%84%20%D9%84%D9%88%D9%82%D9%88%20.png',
            self::SEASON => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1751042067378x555035240410128200/%D8%B3%D9%8A%D8%B2%D9%88%D9%86%20%D9%84%D9%88%D9%82%D9%88%20.png',
            self::ESEVEN_STORE => '//6e199106b3178ff750cc0a7923889ab8.cdn.bubble.io/f1747552053806x192843223739762140/9999%20ee.png',
        };
    }

    /**
     * Get the client ID for the campaign
     */
    public function getClientId(): string
    {
        return match($this) {
            self::SWISS_CORNER => 'STO-X-667',
            self::PHONE_ZONE => 'STO-N-358',
            self::SHIFT => 'STO-G-214',
            self::ALBAROO => 'STO-B-810',
            self::TREASURE_ISLAND => 'STO-Q-162',
            self::ALHAWWAJ => 'STO-M-789',
            self::REBUNE => 'STO-O-774',
            self::TAAM_STORE => 'STO-N-388',
            self::RYEFAL => 'STO-F-352',
            self::SEASON => 'STO-M-686',
            self::ESEVEN_STORE => 'STO-L-197',
        };
    }

    /**
     * Get the website URL for the campaign
     */
    public function getWebsiteUrl(): string
    {
        return match($this) {
            self::SWISS_CORNER => 'https://swisscorner.co',
            self::PHONE_ZONE => 'https://phonezonestore.com',
            self::SHIFT => 'https://shift-saudi.com',
            self::ALBAROO => 'https://albaroo.com',
            self::TREASURE_ISLAND => 'https://treasureisland.sa',
            self::ALHAWWAJ => 'https://alhawwaj.com',
            self::REBUNE => 'https://rebunesastore.com',
            self::TAAM_STORE => 'https://taam-store.com',
            self::RYEFAL => 'https://ryefal.com',
            self::SEASON => 'https://season.sa',
            self::ESEVEN_STORE => 'https://eseven-store.com',
        };
    }

    /**
     * Get all campaigns as an array with all information
     */
    public static function getAllCampaigns(): array
    {
        $campaigns = [];
        foreach (self::cases() as $campaign) {
            $campaigns[] = [
                'id' => $campaign->value,
                'client_name' => $campaign->getClientName(),
                'client_id' => $campaign->getClientId(),
                'logo_url' => $campaign->getLogoUrl(),
                'website_url' => $campaign->getWebsiteUrl(),
            ];
        }
        return $campaigns;
    }

    /**
     * Find campaign by ID
     */
    public static function findById(string $id): ?self
    {
        foreach (self::cases() as $campaign) {
            if ($campaign->value === $id) {
                return $campaign;
            }
        }
        return null;
    }

    /**
     * Get campaign by client ID
     */
    public static function findByClientId(string $clientId): ?self
    {
        foreach (self::cases() as $campaign) {
            if ($campaign->getClientId() === $clientId) {
                return $campaign;
            }
        }
        return null;
    }

    /**
     * Get campaign by client name
     */
    public static function findByClientName(string $clientName): ?self
    {
        foreach (self::cases() as $campaign) {
            if ($campaign->getClientName() === $clientName) {
                return $campaign;
            }
        }
        return null;
    }
}
