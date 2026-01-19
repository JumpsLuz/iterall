<?php
class RedSocial {

    private static $redesSoportadas = [
        'instagram' => [
            'nombre' => 'Instagram',
            'icono' => 'fa-brands fa-instagram',
            'patron' => '/^(https?:\/\/)?(www\.)?instagram\.com\/[a-zA-Z0-9._-]+\/?$/',
            'placeholder' => 'instagram.com/usuario',
            'ayuda' => 'Ejemplo: instagram.com/usuario'
        ],
        'twitter' => [
            'nombre' => 'Twitter/X',
            'icono' => 'fa-brands fa-twitter',
            'patron' => '/^(https?:\/\/)?(www\.)?(twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/?$/',
            'placeholder' => 'twitter.com/usuario',
            'ayuda' => 'Ejemplo: twitter.com/usuario o x.com/usuario'
        ],
        'artstation' => [
            'nombre' => 'ArtStation',
            'icono' => 'fa-brands fa-artstation',
            'patron' => '/^(https?:\/\/)?(www\.)?artstation\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'artstation.com/usuario',
            'ayuda' => 'Ejemplo: artstation.com/usuario'
        ],
        'behance' => [
            'nombre' => 'Behance',
            'icono' => 'fa-brands fa-behance',
            'patron' => '/^(https?:\/\/)?(www\.)?behance\.net\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'behance.net/usuario',
            'ayuda' => 'Ejemplo: behance.net/usuario'
        ],
        'deviantart' => [
            'nombre' => 'DeviantArt',
            'icono' => 'fa-brands fa-deviantart',
            'patron' => '/^(https?:\/\/)?(www\.)?deviantart\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'deviantart.com/usuario',
            'ayuda' => 'Ejemplo: deviantart.com/usuario'
        ],
        'linkedin' => [
            'nombre' => 'LinkedIn',
            'icono' => 'fa-brands fa-linkedin',
            'patron' => '/^(https?:\/\/)?(www\.)?linkedin\.com\/in\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'linkedin.com/in/usuario',
            'ayuda' => 'Ejemplo: linkedin.com/in/usuario'
        ],
        'github' => [
            'nombre' => 'GitHub',
            'icono' => 'fa-brands fa-github',
            'patron' => '/^(https?:\/\/)?(www\.)?github\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'github.com/usuario',
            'ayuda' => 'Ejemplo: github.com/usuario'
        ],
        'youtube' => [
            'nombre' => 'YouTube',
            'icono' => 'fa-brands fa-youtube',
            'patron' => '/^(https?:\/\/)?(www\.)?youtube\.com\/(c\/|channel\/|@)?[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'youtube.com/@canal',
            'ayuda' => 'Ejemplo: youtube.com/@canal o youtube.com/channel/ID'
        ],
        'tiktok' => [
            'nombre' => 'TikTok',
            'icono' => 'fa-brands fa-tiktok',
            'patron' => '/^(https?:\/\/)?(www\.)?tiktok\.com\/@[a-zA-Z0-9._-]+\/?$/',
            'placeholder' => 'tiktok.com/@usuario',
            'ayuda' => 'Ejemplo: tiktok.com/@usuario'
        ],
        'twitch' => [
            'nombre' => 'Twitch',
            'icono' => 'fa-brands fa-twitch',
            'patron' => '/^(https?:\/\/)?(www\.)?twitch\.tv\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'twitch.tv/canal',
            'ayuda' => 'Ejemplo: twitch.tv/canal'
        ],
        'dribbble' => [
            'nombre' => 'Dribbble',
            'icono' => 'fa-brands fa-dribbble',
            'patron' => '/^(https?:\/\/)?(www\.)?dribbble\.com\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'dribbble.com/usuario',
            'ayuda' => 'Ejemplo: dribbble.com/usuario'
        ],
        'pinterest' => [
            'nombre' => 'Pinterest',
            'icono' => 'fa-brands fa-pinterest',
            'patron' => '/^(https?:\/\/)?(www\.)?pinterest\.(com|cl|es)\/[a-zA-Z0-9_-]+\/?$/',
            'placeholder' => 'pinterest.com/usuario',
            'ayuda' => 'Ejemplo: pinterest.com/usuario'
        ],
        'otro' => [
            'nombre' => 'Otro sitio web',
            'icono' => 'fa-solid fa-link',
            'patron' => '/^(https?:\/\/)?.+\..+/',
            'placeholder' => 'tusitio.com',
            'ayuda' => 'Cualquier URL válida'
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
        
        // Limpiar URL
        $url = trim($url);
        if (empty($url)) {
            return [
                'valido' => false,
                'mensaje' => 'URL vacía'
            ];
        }
        
        // Si no tiene https://, agregarlo para validación
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        if (preg_match($red['patron'], $url)) {
            return [
                'valido' => true,
                'mensaje' => 'URL válida para ' . $red['nombre'],
                'url_procesada' => $url
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
                // Usar la URL procesada (con https:// si es necesario)
                $validas[$tipo] = $resultado['url_procesada'] ?? trim($url);
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