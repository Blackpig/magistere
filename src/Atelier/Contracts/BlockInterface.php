<?php

namespace BlackpigCreatif\Magistere\Atelier\Contracts;

/**
 * Contract for Magistere blocks registered with the Atelier page builder.
 *
 * Implement this interface (or the Atelier-provided block base class) in each
 * block class. When Atelier is installed the blocks are registered via
 * MagistereServiceProvider::conditionallyRegisterAtelier().
 */
interface BlockInterface
{
    /**
     * Unique block identifier used by Atelier's block registry.
     */
    public static function blockName(): string;

    /**
     * Human-readable label shown in the Atelier block picker.
     */
    public static function blockLabel(): string;
}
