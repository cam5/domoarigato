<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * A piece of HTML that is already written, and is output exactly as given.
 *
 * Nothing is escaped or checked. Only use it for markup you trust.
 */
class RawHtml implements NodeInterface
{
    use Traits\CastsToString;

    /**
     * The markup.
     *
     * @var string
     */
    protected string $html;

    /**
     * Constructor
     *
     * @param string $html The markup.
     */
    public function __construct(string $html)
    {
        $this->html = $html;
    }//end __construct()

    /**
     * A best effort at the text inside the markup: tags stripped, entities decoded.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return html_entity_decode(strip_tags($this->html), (ENT_QUOTES | ENT_HTML5), 'UTF-8');
    }//end getTextContent()

    /**
     * Output the markup, untouched.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->html;
    }//end render()
}//end class
