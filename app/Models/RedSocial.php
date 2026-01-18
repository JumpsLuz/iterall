<?php
class RedSocial {

    private static $redesSoportadas = [
        'instagram' => [
            'nombre' => 'Instagram',
            'icono' => 'fa-brands fa-instagram',
            'patron' => '/^https?:\/\/(www\.)?instagram\.com\/[a-zA-Z0-9._]+\/?$/',
            'placeholder' => 'https://instagram.com/tuusuario',
            'ayuda' => 'Formato: https://instagram.com/usuario'
        ],
        'twitter' => [
            'nombre' => 'Twitter/X',
            'icono' => 'fa-brands fa-twitter',
            'patron' => '/^https?:\/\/(www\.)?(twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/?$/',
            'placeholder' => 'https://twitter.com/tuusuario',
            'ayuda' => 'Formato: https://twitter.com/usuario o https://x.com/usuario'
        ],
        'artstation' => [
            'nombre' => 'ArtStation',
            'icono' => 'fa-brands fa-artstation',
            'patron' => '/^https?:\/\/(www\.)?artstation\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://artstation.com/tuusuario',
            'ayuda' => 'Formato: https://artstation.com/usuario'
        ],
        'behance' => [
            'nombre' => 'Behance',
            'icono' => 'fa-brands fa-behance',
            'patron' => '/^https?:\/\/(www\.)?behance\.net\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://behance.net/tuusuario',
            'ayuda' => 'Formato: https://behance.net/usuario'
        ],
        'deviantart' => [
            'nombre' => 'DeviantArt',
            'icono' => 'fa-brands fa-deviantart',
            'patron' => '/^https?:\/\/(www\.)?deviantart\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://deviantart.com/tuusuario',
            'ayuda' => 'Formato: https://deviantart.com/usuario'
        ],
        'linkedin' => [
            'nombre' => 'LinkedIn',
            'icono' => 'fa-brands fa-linkedin',
            'patron' => '/^https?:\/\/(www\.)?linkedin\.com\/in\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://linkedin.com/in/tuusuario',
            'ayuda' => 'Formato: https://linkedin.com/in/usuario'
        ],
        'github' => [
            'nombre' => 'GitHub',
            'icono' => 'fa-brands fa-github',
            'patron' => '/^https?:\/\/(www\.)?github\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://github.com/tuusuario',
            'ayuda' => 'Formato: https://github.com/usuario'
        ],
        'youtube' => [
            'nombre' => 'YouTube',
            'icono' => 'fa-brands fa-youtube',
            'patron' => '/^https?:\/\/(www\.)?youtube\.com\/(c\/|channel\/|@)?[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://youtube.com/@tucanal',
            'ayuda' => 'Formato: https://youtube.com/@canal o /channel/ID'
        ],
        'tiktok' => [
            'nombre' => 'TikTok',
            'icono' => 'fa-brands fa-tiktok',
            'patron' => '/^https?:\/\/(www\.)?tiktok\.com\/@[a-zA-Z0-9._]+\/?$/',
            'placeholder' => 'https://tiktok.com/@tuusuario',
            'ayuda' => 'Formato: https://tiktok.com/@usuario'
        ],
        'twitch' => [
            'nombre' => 'Twitch',
            'icono' => 'fa-brands fa-twitch',
            'patron' => '/^https?:\/\/(www\.)?twitch\.tv\/[a-zA-Z0-9_]+\/?$/',
            'placeholder' => 'https://twitch.tv/tucanal',
            'ayuda' => 'Formato: https://twitch.tv/canal'
        ],
        'dribbble' => [
            'nombre' => 'Dribbble',
            'icono' => 'fa-brands fa-dribbble',
            'patron' => '/^https?:\/\/(www\.)?dribbble\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://dribbble.com/tuusuario',
            'ayuda' => 'Formato: https://dribbble.com/usuario'
        ],
        'pinterest' => [
            'nombre' => 'Pinterest',
            'icono' => 'fa-brands fa-pinterest',
            'patron' => '/^https?:\/\/(www\.)?pinterest\.(com|cl|es)\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'https://pinterest.com/tuusuario',
            'ayuda' => 'Formato: https://pinterest.com/usuario'
        ],
        'otro' => [
            'nombre' => 'Otro sitio web',
            'icono' => 'fa-solid fa-link',
            'patron' => '/^https?:\/\/.+$/',
            'placeholder' => 'https://tusitio.com',
            'ayuda' => 'Cualquier URL válida con https://'
        ]
    ];

    public static function obtenerRedesSoportadas() {
        return self::$redesSoportadas;
    }

    /**
     * @param string $tipo 
     * @param string $url 
     * @return array 
     */
    public static function validar($tipo, $url) {
        if (!isset(self::$redesSoportadas[$tipo])) {
            return [
                'valido' => false,
                'mensaje' => 'Red social no soportada'
            ];
        }

        $red = self::$redesSoportadas[$tipo];
        
        if (preg_match($red['patron'], $url)) {
            return [
                'valido' => true,
                'mensaje' => 'URL válida para ' . $red['nombre']
            ];
        }

        return [
            'valido' => false,
            'mensaje' => 'URL inválida. ' . $red['ayuda']
        ];
    }

    /**
     * @param array $redes 
     * @return array 
     */
    public static function validarMultiples($redes) {
        $validas = [];
        $errores = [];

        foreach ($redes as $tipo => $url) {
            if (empty(trim($url))) {
                continue;
            }

            $resultado = self::validar($tipo, trim($url));
            
            if ($resultado['valido']) {
                $validas[$tipo] = trim($url);
            } else {
                $errores[$tipo] = $resultado['mensaje'];
            }
        }

        return [
            'validas' => $validas,
            'errores' => $errores
        ];
    }

    /**
     * @param string $url
     * @return string
     */
    public static function extraerUsuario($url) {
        $url = preg_replace('/^https?:\/\/(www\.)?/', '', $url);
        
        $partes = explode('/', $url);
        
        return isset($partes[1]) && !empty($partes[1]) ? $partes[1] : $partes[0];
    }
}