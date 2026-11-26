<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class NewDaIconRegistry
{
    /** @var list<string> */
    public const CATEGORIES = ['races', 'weapons', 'powers'];

    /** @var list<string> */
    private const CUSTOM_EXTENSIONS = ['svg', 'webp', 'png'];

    /** @var array<string, array<string, string>> */
    private const DEFAULT_ICONS = [
        'races' => [
            'human' => '<circle cx="24" cy="24" r="16" fill="none" stroke="currentColor" stroke-width="2.4"/><polygon points="24,8 28.5,20 40,24 28.5,28 24,40 19.5,28 8,24 19.5,20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><circle cx="24" cy="24" r="2.8" fill="currentColor"/>',
            'elf' => '<path d="M11 37 C11 19 22 10 37 10 C37 25 28 37 11 37 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="M11 37 L31 17 M21 27 L19 20 M26 22 L32 23" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>',
            'demon' => '<path d="M11 11 C7 22 14 31 24 34 C34 31 41 22 37 11 C33 19 29 22 24 22 C19 22 15 19 11 11 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><polygon points="24,26 28,32 24,38 20,32" fill="currentColor"/>',
            'robot' => '<circle cx="24" cy="24" r="10.5" fill="none" stroke="currentColor" stroke-width="2.6"/><circle cx="24" cy="24" r="4.2" fill="currentColor"/><path d="M24 6 V11 M24 37 V42 M6 24 H11 M37 24 H42 M11.3 11.3 L14.8 14.8 M33.2 33.2 L36.7 36.7 M36.7 11.3 L33.2 14.8 M14.8 33.2 L11.3 36.7" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>',
            'mutant' => '<path d="M14 9 C34 15 14 33 34 39 M34 9 C14 15 34 33 14 39" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/><path d="M17 15 H31 M19 24 H29 M17 33 H31" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>',
            'golem' => '<polygon points="24,7 38,16 35,39 13,39 10,16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/><path d="M24 7 V22 L19 28 L25 34" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
            'vampire' => '<path d="M8 20 C12 13 18 14 21 19 L24 14 L27 19 C30 14 36 13 40 20 C35 23 33 27 32 32 C29 28 26 28 24 33 C22 28 19 28 16 32 C15 27 13 23 8 20 Z" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linejoin="round"/><path d="M24 34 C24 34 21 38 21 39.5 C21 41.2 22.3 42.2 24 42.2 C25.7 42.2 27 41.2 27 39.5 C27 38 24 34 24 34 Z" fill="currentColor"/>',
            'alien' => '<circle cx="24" cy="24" r="14" fill="none" stroke="currentColor" stroke-width="2.2"/><ellipse cx="24" cy="24" rx="17" ry="6.5" transform="rotate(-28 24 24)" fill="none" stroke="currentColor" stroke-width="2.2"/><circle cx="24" cy="24" r="3.5" fill="currentColor"/><circle cx="35" cy="14" r="2.2" fill="currentColor"/>',
        ],
        'weapons' => [
            'sword' => '<path d="M24 7 L28 14 V30 H20 V14 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M13 30 H35 M24 30 V39" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/><circle cx="24" cy="40.5" r="2.5" fill="currentColor"/>',
            'bow' => '<path d="M16 9 C32 14 32 34 16 39" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/><line x1="16" y1="10" x2="16" y2="38" stroke="currentColor" stroke-width="1.8"/><path d="M10 24 H37 M31 19 L37 24 L31 29" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>',
            'hammer' => '<rect x="11" y="10" width="26" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M24 22 V40 M18 10 V22 M30 10 V22" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
            'spear' => '<polygon points="24,6 30,19 24,16 18,19" fill="currentColor"/><path d="M24 15 V41 M19 23 L29 23" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
            'dagger' => '<path d="M35 10 C28 13 21 19 18 27 L23 30 C29 25 34 18 35 10 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M14 25 L25 34 M18 30 L12 37" stroke="currentColor" stroke-width="2.7" stroke-linecap="round"/>',
            'staff' => '<path d="M24 21 V41" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/><polygon points="24,7 30,14 24,21 18,14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M14 14 C14 20 19 24 24 24 C29 24 34 20 34 14" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>',
            'gun' => '<path d="M9 17 H37 V23 H24 L21 28 H15 L12 37 H7 L11 23 H9 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M22 23 L24 28" stroke="currentColor" stroke-width="2"/>',
            'grimoire' => '<path d="M8 13 C14 11 20 12 24 15 C28 12 34 11 40 13 V36 C34 34 28 35 24 38 C20 35 14 34 8 36 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><line x1="24" y1="15" x2="24" y2="38" stroke="currentColor" stroke-width="2.1"/><polygon points="16,20 17.5,23.5 21,25 17.5,26.5 16,30 14.5,26.5 11,25 14.5,23.5" fill="currentColor"/>',
        ],
        'powers' => [
            'fire' => '<path d="M24 7 C30 16 36 21 36 29 C36 36 30.8 41 24 41 C17.2 41 12 36 12 29 C12 23 16 19 19 16 C18 20 20 22 22 22 C20 16 21 11 24 7 Z" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/><path d="M24 26 C27 29 28 32 28 34.5 C28 36.8 26.2 38.2 24 38.2 C21.8 38.2 20 36.8 20 34.5 C20 32 21.5 29 24 26 Z" fill="currentColor"/>',
            'poison' => '<path d="M19 9 H29 M21 9 V17 L13 31 C11.5 34 13.5 39 17.5 39 H30.5 C34.5 39 36.5 34 35 31 L27 17 V9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 30 H33" stroke="currentColor" stroke-width="2.2"/><circle cx="21" cy="34" r="2" fill="currentColor"/><circle cx="27" cy="33" r="1.6" fill="currentColor"/>',
            'ice' => '<path d="M24 7 V41 M9.3 15.5 L38.7 32.5 M38.7 15.5 L9.3 32.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><path d="M20 11 L24 15 L28 11 M20 37 L24 33 L28 37 M12 20 L16 20 L14 16 M36 28 L32 28 L34 32 M34 16 L32 20 L36 20 M14 32 L16 28 L12 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            'lightning' => '<polygon points="27,7 13,26 23,26 20,41 35,21 25,21" fill="currentColor" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
            'regeneration' => '<path d="M24 39 C13 32 9 25 9 18.5 C9 13.5 13 10 17.5 10 C20.5 10 22.8 11.6 24 14 C25.2 11.6 27.5 10 30.5 10 C35 10 39 13.5 39 18.5 C39 25 35 32 24 39 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="M24 18 V29 M18.5 23.5 H29.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
            'gravity' => '<circle cx="24" cy="24" r="7.5" fill="currentColor"/><ellipse cx="24" cy="24" rx="18" ry="7" transform="rotate(-24 24 24)" fill="none" stroke="currentColor" stroke-width="2.5"/><circle cx="38" cy="17" r="2.6" fill="currentColor"/>',
            'teleportation' => '<circle cx="18" cy="20" r="9.5" fill="none" stroke="currentColor" stroke-width="2.4"/><circle cx="30" cy="28" r="9.5" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M12 36 L36 12 M29 12 H36 V19" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>',
            'invisibility' => '<path d="M7 24 Q24 11 41 24 Q24 37 7 24 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><circle cx="24" cy="24" r="5.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-dasharray="3 2.5"/><line x1="11" y1="37" x2="37" y2="11" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
        ],
    ];

    /** @var array<string, string> */
    private const POWER_SIGILS = [
        'fire' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="0.9" stroke-dasharray="3 3"/><polygon points="60,10 70,42 104,42 76,62 86,96 60,76 34,96 44,62 16,42 50,42" fill="none" stroke="currentColor" stroke-width="1.3"/><polygon points="60,18 72,48 102,60 72,72 60,102 48,72 18,60 48,48" fill="none" stroke="currentColor" stroke-width="1.1"/>',
        'poison' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="36" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="20" fill="none" stroke="currentColor" stroke-width="1.2" stroke-dasharray="5 3"/><path d="M60 12 C86 12,102 36,92 60 C82 84,54 96,36 82 C20 70,22 44,40 34 C56 25,74 36,72 54 C70 68,54 74,48 64" fill="none" stroke="currentColor" stroke-width="1.5"/>',
        'ice' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,12 102,84 18,84" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,108 18,36 102,36" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="14" fill="none" stroke="currentColor" stroke-width="1.2"/>',
        'lightning' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="24" y="24" width="72" height="72" fill="none" stroke="currentColor" stroke-width="1.2"/><rect x="24" y="24" width="72" height="72" transform="rotate(45 60 60)" fill="none" stroke="currentColor" stroke-width="1.2"/><path d="M66 16 L46 58 L64 58 L52 104" fill="none" stroke="currentColor" stroke-width="1.8"/>',
        'regeneration' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="44" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="76" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M60 22 C72 40,72 80,60 98 C48 80,48 40,60 22 Z" fill="none" stroke="currentColor" stroke-width="1.3"/>',
        'gravity' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(-24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="16" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="60" cy="60" r="5" fill="currentColor"/>',
        'teleportation' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="46" cy="52" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="74" cy="68" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M26 94 L94 26 M84 26 L94 26 L94 36" fill="none" stroke="currentColor" stroke-width="1.3"/>',
        'invisibility' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.3" stroke-dasharray="6 4"/><circle cx="60" cy="60" r="38" fill="none" stroke="currentColor" stroke-width="1.1" stroke-dasharray="3 4"/><path d="M18 60 Q60 26 102 60 Q60 94 18 60 Z" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="11" fill="none" stroke="currentColor" stroke-width="1.3"/><line x1="24" y1="96" x2="96" y2="24" stroke="currentColor" stroke-width="1.2"/>',
    ];

    private string $iconsDirectory;

    private string $publicPrefix;

    public function __construct(?string $iconsDirectory = null, string $publicPrefix = 'images/newda/icons')
    {
        $this->iconsDirectory = rtrim($iconsDirectory ?? public_path($publicPrefix), '/\\');
        $this->publicPrefix = trim($publicPrefix, '/');
    }

    /**
     * @return array{type: 'image'|'svg', format: string, url: ?string, viewBox: string, markup: string}
     */
    public function resolve(string $category, string $id): array
    {
        if (! in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException("Catégorie d'icône inconnue : {$category}");
        }

        $safeId = preg_replace('/[^a-z0-9_-]/i', '', strtolower($id)) ?? '';
        $fallbackMarkup = self::DEFAULT_ICONS[$category][$safeId]
            ?? reset(self::DEFAULT_ICONS[$category]);

        if ($safeId !== '') {
            foreach (self::CUSTOM_EXTENSIONS as $ext) {
                $fullPath = $this->iconsDirectory.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR.$safeId.'.'.$ext;
                if (is_file($fullPath)) {
                    return [
                        'type' => 'image',
                        'format' => $ext,
                        'url' => asset("{$this->publicPrefix}/{$category}/{$safeId}.{$ext}"),
                        'viewBox' => '0 0 48 48',
                        'markup' => $fallbackMarkup,
                    ];
                }
            }
        }

        return [
            'type' => 'svg',
            'format' => 'svg',
            'url' => null,
            'viewBox' => '0 0 48 48',
            'markup' => $fallbackMarkup,
        ];
    }

    /**
     * @return array{races: array<string, array<string, mixed>>, weapons: array<string, array<string, mixed>>, powers: array<string, array<string, mixed>>}
     */
    public function allByCategory(): array
    {
        $result = [
            'races' => [],
            'weapons' => [],
            'powers' => [],
        ];

        foreach (self::CATEGORIES as $category) {
            foreach (array_keys(self::DEFAULT_ICONS[$category]) as $id) {
                $result[$category][$id] = $this->resolve($category, $id);
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    public function powerSigils(): array
    {
        return self::POWER_SIGILS;
    }

    public function renderInlineIcon(string $category, string $id, string $class = 'codex-icon', ?string $alt = null): string
    {
        $icon = $this->resolve($category, $id);
        $escapedClass = e($class);

        if ($icon['type'] === 'image' && $icon['url'] !== null) {
            $escapedUrl = e($icon['url']);
            $escapedAlt = e($alt ?? $id);

            return '<img class="'.$escapedClass.' codex-icon--custom" src="'.$escapedUrl.'" alt="'.$escapedAlt.'" loading="lazy">';
        }

        return '<svg class="'.$escapedClass.'" viewBox="'.$icon['viewBox'].'" aria-hidden="true">'.$icon['markup'].'</svg>';
    }

    public function renderPowerSigil(string $powerId, string $hex, string $class = 'codex-sigil-watermark'): string
    {
        $paths = self::POWER_SIGILS[$powerId] ?? self::POWER_SIGILS['invisibility'];

        return '<svg class="'.e($class).'" viewBox="0 0 120 120" style="color: '.e($hex).';" aria-hidden="true">'.$paths.'</svg>';
    }
}
