<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * An HTML comment.
 */
class Comment implements NodeInterface
{
    use Traits\CastsToString;

    /**
     * The text of the comment.
     *
     * @var string
     */
    protected string $text;

    /**
     * Constructor
     *
     * @param string $text The text of the comment.
     *
     * @throws \InvalidArgumentException When the text would end the comment early, or isn't allowed in one.
     */
    public function __construct(string $text)
    {
        if (true === str_starts_with($text, '>')
            || true === str_starts_with($text, '->')
            || true === str_contains($text, '<!--')
            || true === str_contains($text, '-->')
            || true === str_contains($text, '--!>')
            || true === str_ends_with($text, '<!-')
        ) {
            throw new \InvalidArgumentException('That text cannot be written inside of an HTML comment.');
        }

        $this->text = $text;
    }//end __construct()

    /**
     * Retrieve the text of the comment.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }//end getText()

    /**
     * Comments aren't shown to the reader, so they have no text content.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return '';
    }//end getTextContent()

    /**
     * Output the comment.
     *
     * @return string
     */
    public function render(): string
    {
        return '<!--'.$this->text.'-->';
    }//end render()
}//end class
