<?php
/**
 * Theme-wide constants (identifier, menu locations, image sizes).
 *
 * @package Happywood
 */

const HAPPYWOOD_IDENTIFIER = 'happywood';
const HAPPYWOOD_PREFIX     = 'happywood_';

/** @var string Primary navigation menu location (categories + right-pulled items). */
const HAPPYWOOD_MENU_PRIMARY = 'happywood-primary-nav';

/** @var string Utility navigation menu location (Showrooms, Account, Quotes). */
const HAPPYWOOD_MENU_UTILITY = 'happywood-utility-nav';

/** @var string Registered image size for desktop mega menu thumbnails (2× display). */
const HAPPYWOOD_MENU_THUMB_SIZE = 'happywood-menu-thumb';

/** @var string Theme asset used when a menu thumb has no category/override image. */
const HAPPYWOOD_MENU_THUMB_PLACEHOLDER = 'assets/images/menu-thumb-placeholder.png';

/** @var int Display size (px) for menu thumbnails in CSS — 2× source uses HAPPYWOOD_MENU_THUMB_SIZE. */
const HAPPYWOOD_MENU_THUMB_DISPLAY = 54;
