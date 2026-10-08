<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Enums;

use Cam5\Domoarigato\Elements as El;

/**
 * Static Enum of elements explicitly supported.
 *
 * This is every element in the index of the HTML Living Standard. Anything else (custom elements,
 * the elements inside of an <svg>) still works, as a generic element with an opening and closing tag.
 *
 * @see https://html.spec.whatwg.org/multipage/indices.html#elements-3
 */
class Elements extends StaticEnum
{
    const A               = 'a';
    const ABBR            = 'abbr';
    const ADDRESS         = 'address';
    const AREA            = 'area';
    const ARTICLE         = 'article';
    const ASIDE           = 'aside';
    const AUDIO           = 'audio';
    const B               = 'b';
    const BASE            = 'base';
    const BDI             = 'bdi';
    const BDO             = 'bdo';
    const BLOCKQUOTE      = 'blockquote';
    const BODY            = 'body';
    const BR              = 'br';
    const BUTTON          = 'button';
    const CANVAS          = 'canvas';
    const CAPTION         = 'caption';
    const CITE            = 'cite';
    const CODE            = 'code';
    const COL             = 'col';
    const COLGROUP        = 'colgroup';
    const DATA            = 'data';
    const DATALIST        = 'datalist';
    const DD              = 'dd';
    const DEL             = 'del';
    const DETAILS         = 'details';
    const DFN             = 'dfn';
    const DIALOG          = 'dialog';
    const DIV             = 'div';
    const DL              = 'dl';
    const DT              = 'dt';
    const EM              = 'em';
    const EMBED           = 'embed';
    const FIELDSET        = 'fieldset';
    const FIGCAPTION      = 'figcaption';
    const FIGURE          = 'figure';
    const FOOTER          = 'footer';
    const FORM            = 'form';
    const H1              = 'h1';
    const H2              = 'h2';
    const H3              = 'h3';
    const H4              = 'h4';
    const H5              = 'h5';
    const H6              = 'h6';
    const HEAD            = 'head';
    const HEADER          = 'header';
    const HGROUP          = 'hgroup';
    const HR              = 'hr';
    const HTML            = 'html';
    const I               = 'i';
    const IFRAME          = 'iframe';
    const IMG             = 'img';
    const INPUT           = 'input';
    const INS             = 'ins';
    const KBD             = 'kbd';
    const LABEL           = 'label';
    const LEGEND          = 'legend';
    const LI              = 'li';
    const LINK            = 'link';
    const MAIN            = 'main';
    const MAP             = 'map';
    const MARK            = 'mark';
    const MATH            = 'math';
    const MENU            = 'menu';
    const META            = 'meta';
    const METER           = 'meter';
    const NAV             = 'nav';
    const NOSCRIPT        = 'noscript';
    const OBJECT          = 'object';
    const OL              = 'ol';
    const OPTGROUP        = 'optgroup';
    const OPTION          = 'option';
    const OUTPUT          = 'output';
    const P               = 'p';
    const PICTURE         = 'picture';
    const PRE             = 'pre';
    const PROGRESS        = 'progress';
    const Q               = 'q';
    const RP              = 'rp';
    const RT              = 'rt';
    const RUBY            = 'ruby';
    const S               = 's';
    const SAMP            = 'samp';
    const SCRIPT          = 'script';
    const SEARCH          = 'search';
    const SECTION         = 'section';
    const SELECT          = 'select';
    const SELECTEDCONTENT = 'selectedcontent';
    const SLOT            = 'slot';
    const SMALL           = 'small';
    const SOURCE          = 'source';
    const SPAN            = 'span';
    const STRONG          = 'strong';
    const STYLE           = 'style';
    const SUB             = 'sub';
    const SUMMARY         = 'summary';
    const SUP             = 'sup';
    const SVG             = 'svg';
    const TABLE           = 'table';
    const TBODY           = 'tbody';
    const TD              = 'td';
    const TEMPLATE        = 'template';
    const TEXTAREA        = 'textarea';
    const TFOOT           = 'tfoot';
    const TH              = 'th';
    const THEAD           = 'thead';
    const TIME            = 'time';
    const TITLE           = 'title';
    const TR              = 'tr';
    const TRACK           = 'track';
    const U               = 'u';
    const UL              = 'ul';
    const VAR             = 'var';
    const VIDEO           = 'video';
    const WBR             = 'wbr';

    /**
     * Map of names to classnames.
     *
     * @var array
     */
    protected static array $keys = [
        self::A               => El\A::class,
        self::ABBR            => El\Abbr::class,
        self::ADDRESS         => El\Address::class,
        self::AREA            => El\Area::class,
        self::ARTICLE         => El\Article::class,
        self::ASIDE           => El\Aside::class,
        self::AUDIO           => El\Audio::class,
        self::B               => El\B::class,
        self::BASE            => El\Base::class,
        self::BDI             => El\Bdi::class,
        self::BDO             => El\Bdo::class,
        self::BLOCKQUOTE      => El\Blockquote::class,
        self::BODY            => El\Body::class,
        self::BR              => El\Br::class,
        self::BUTTON          => El\Button::class,
        self::CANVAS          => El\Canvas::class,
        self::CAPTION         => El\Caption::class,
        self::CITE            => El\Cite::class,
        self::CODE            => El\Code::class,
        self::COL             => El\Col::class,
        self::COLGROUP        => El\Colgroup::class,
        self::DATA            => El\Data::class,
        self::DATALIST        => El\Datalist::class,
        self::DD              => El\Dd::class,
        self::DEL             => El\Del::class,
        self::DETAILS         => El\Details::class,
        self::DFN             => El\Dfn::class,
        self::DIALOG          => El\Dialog::class,
        self::DIV             => El\Div::class,
        self::DL              => El\Dl::class,
        self::DT              => El\Dt::class,
        self::EM              => El\Em::class,
        self::EMBED           => El\Embed::class,
        self::FIELDSET        => El\Fieldset::class,
        self::FIGCAPTION      => El\Figcaption::class,
        self::FIGURE          => El\Figure::class,
        self::FOOTER          => El\Footer::class,
        self::FORM            => El\Form::class,
        self::H1              => El\H1::class,
        self::H2              => El\H2::class,
        self::H3              => El\H3::class,
        self::H4              => El\H4::class,
        self::H5              => El\H5::class,
        self::H6              => El\H6::class,
        self::HEAD            => El\Head::class,
        self::HEADER          => El\Header::class,
        self::HGROUP          => El\Hgroup::class,
        self::HR              => El\Hr::class,
        self::HTML            => El\Html::class,
        self::I               => El\I::class,
        self::IFRAME          => El\Iframe::class,
        self::IMG             => El\Img::class,
        self::INPUT           => El\Input::class,
        self::INS             => El\Ins::class,
        self::KBD             => El\Kbd::class,
        self::LABEL           => El\Label::class,
        self::LEGEND          => El\Legend::class,
        self::LI              => El\Li::class,
        self::LINK            => El\Link::class,
        self::MAIN            => El\Main::class,
        self::MAP             => El\Map::class,
        self::MARK            => El\Mark::class,
        self::MATH            => El\Math::class,
        self::MENU            => El\Menu::class,
        self::META            => El\Meta::class,
        self::METER           => El\Meter::class,
        self::NAV             => El\Nav::class,
        self::NOSCRIPT        => El\Noscript::class,
        self::OBJECT          => El\ObjectElement::class,
        self::OL              => El\Ol::class,
        self::OPTGROUP        => El\Optgroup::class,
        self::OPTION          => El\Option::class,
        self::OUTPUT          => El\Output::class,
        self::P               => El\P::class,
        self::PICTURE         => El\Picture::class,
        self::PRE             => El\Pre::class,
        self::PROGRESS        => El\Progress::class,
        self::Q               => El\Q::class,
        self::RP              => El\Rp::class,
        self::RT              => El\Rt::class,
        self::RUBY            => El\Ruby::class,
        self::S               => El\S::class,
        self::SAMP            => El\Samp::class,
        self::SCRIPT          => El\Script::class,
        self::SEARCH          => El\Search::class,
        self::SECTION         => El\Section::class,
        self::SELECT          => El\Select::class,
        self::SELECTEDCONTENT => El\Selectedcontent::class,
        self::SLOT            => El\Slot::class,
        self::SMALL           => El\Small::class,
        self::SOURCE          => El\Source::class,
        self::SPAN            => El\Span::class,
        self::STRONG          => El\Strong::class,
        self::STYLE           => El\Style::class,
        self::SUB             => El\Sub::class,
        self::SUMMARY         => El\Summary::class,
        self::SUP             => El\Sup::class,
        self::SVG             => El\Svg::class,
        self::TABLE           => El\Table::class,
        self::TBODY           => El\Tbody::class,
        self::TD              => El\Td::class,
        self::TEMPLATE        => El\Template::class,
        self::TEXTAREA        => El\Textarea::class,
        self::TFOOT           => El\Tfoot::class,
        self::TH              => El\Th::class,
        self::THEAD           => El\Thead::class,
        self::TIME            => El\Time::class,
        self::TITLE           => El\Title::class,
        self::TR              => El\Tr::class,
        self::TRACK           => El\Track::class,
        self::U               => El\U::class,
        self::UL              => El\Ul::class,
        self::VAR             => El\VarElement::class,
        self::VIDEO           => El\Video::class,
        self::WBR             => El\Wbr::class,
    ];
}//end class
