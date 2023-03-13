<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function is_string;
use function preg_replace_callback;
use function sprintf;

class WrapQuotedText extends Filter
{
    /**
     * General spacers for time and date.
     */
    private string $spacers = '[\\s,/\\.\\-]';

    /**
     * Matches a time.
     */
    private string $timePattern = '(?:[0-2])?[0-9]:[0-5][0-9](?::[0-5][0-9])?(?:(?:\\s)?[AP]M)?';

    /**
     * Matches a day of the week.
     */
    private string $dayPattern = '(?:(?:Mon(?:day)?)|(?:Tue(?:sday)?)|(?:Wed(?:nesday)?)|(?:Thu(?:rsday)?)|(?:Fri(?:day)?)|(?:Sat(?:urday)?)|(?:Sun(?:day)?))';

    /**
     * Matches day of the month (number and st, nd, rd, th).
     */
    private string $dayOfMonthPattern;

    /**
     * Matches months (numeric and text).
     */
    private string $monthPattern = '(?:(?:Jan(?:uary)?)|(?:Feb(?:r?uary)?)|(?:Mar(?:ch)?)|(?:Apr(?:il)?)|(?:May)|(?:Jun(?:e)?)|(?:Jul(?:y)?)|(?:Aug(?:ust)?)|(?:Sep(?:tember)?)|(?:Oct(?:ober)?)|(?:Nov(?:ember)?)|(?:Dec(?:ember)?)|(?:[0-1]?[0-9]))';

    /**
     * Matches years (only 1000's and 2000's, because we are matching emails).
     */
    private string $yearPattern = '(?:[1-2]?[0-9])[0-9][0-9]';

    /**
     * Matches a full date.
     */
    private string $datePattern;

    /**
     * Matches a date and time combo (in either order).
     */
    private string $dateTimePattern;

    /**
     * Matches a leading line such as  ----Original Message---- or ------------------------
     */
    private string $leadInLine = '(?:-+\\s*(?:Original(?:\\sMessage)?|Messaggio originale|Mensaje original|Исходное сообщение|Mensagem original|پیام اصلی|Pesan asli|Ursprüngliche Nachricht|Message d(?:’|\'|&#039;)origine|Oorspronkelijke bericht|Oprindelige meddelelse|原始郵件|原始邮件)?\\s*-+|_+)\\n';

    /**
     * Matches a header line indicating the date.
     */
    private string $dateLine;

    /**
     * Matches a subject or address line.
     */
    private string $subjectOrAddressLine = "((?:from|Mittente|Von|De)|(?:subject|Oggetto|Betreff|objet)|(?:b?cc)|(?:to))|\\s*:.*\n";

    /**
     * Matches gmail style quoted text beginning i.e. On Mon Jun 7, 2010 at 8:50 PM, Simon wrote:
     */
    private string $gmailQuotedTextBeginning;

    /**
     * Matches the start of a quoted section of an email.
     */
    private string $QUOTED_TEXT_REGEX;

    public function __construct()
    {
        $this->dayOfMonthPattern = '[0-3]?[0-9]' . $this->spacers . '*(?:(?:th)|(?:st)|(?:nd)|(?:rd))?';
        $this->datePattern = '(?:' . $this->dayPattern . $this->spacers . '+)?(?:(?:' . $this->dayOfMonthPattern
            . $this->spacers . '+' . $this->monthPattern . ')|'
            . '(?:' . $this->monthPattern . $this->spacers . '+' . $this->dayOfMonthPattern . '))'
            . $this->spacers . '+' . $this->yearPattern;
        $this->dateTimePattern = '(?:' . $this->datePattern . '[\\s,]*(?:(?:at)|(?:@))?\\s*' . $this->timePattern . ')|'
            . '(?:' . $this->timePattern . '[\\s,]*(?:on)?\\s*' . $this->datePattern . ')';
        $this->dateLine = '(?:(?:date|data|Gesendet|Envoyé)|(?:sent)|(?:time)):\\s*' . $this->dateTimePattern . ".*\n";
        $this->gmailQuotedTextBeginning = '(On\\s+' . $this->dateTimePattern . ".*wrote:\n)";
        $this->QUOTED_TEXT_REGEX = '`^(?i)(?:(?:' . $this->leadInLine . ')?(?:(?:' . $this->subjectOrAddressLine
            . ')|(?:' . $this->dateLine . ')){2,6})|(?:' . $this->gmailQuotedTextBeginning . ')`um';
    }

    /**
     * Wrap quoted text in an expandable tag.
     */
    public function preProcess(string $text): string
    {
        // Find the first matching quoted boundary.
        $result = preg_replace_callback(
            $this->QUOTED_TEXT_REGEX,
            function ($matches) {
                // Wrap what's matched in an expandable tag.
                return sprintf('<div class="expandable"></div><div class="supportpal_quote">%s', $matches[0]);
            },
            $text,
            1, // Only replace the first match.
            $count // Total number of replacements that were performed.
        );

        // Make sure the result was a non-empty string and that a replacement was actually performed (count as been
        // incremented), otherwise return what we were given.
        return empty($result) || $count !== 1 || ! is_string($result) ? $text : sprintf('%s</div>', $result);
    }
}
