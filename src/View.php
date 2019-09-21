<?php

namespace ogheo\htmlcompress;

/**
 * Class View
 * @package ogheo\htmlcompress
 */
class View extends \yii\web\View
{
    /**
     * Enable or disable compression, by default compression is enabled.
     *
     * @var bool
     */
    public $compress = true;
    public $exclude = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->compress === true) {
            \Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function (\yii\base\Event $Event) {
                if (!empty($this->exclude)) {
                    preg_match(
                        $this->exclude, // '/\/load\/0-0-0-(.*?)-13/'
                        \Yii::$app->request->url,
                        $matches,
                        PREG_OFFSET_CAPTURE
                    );
                }
                if (!isset($matches[1]) || empty($matches[1])) {
                    $Response = $Event->sender;
                    if ($Response->format === \yii\web\Response::FORMAT_HTML) {
                        if (!empty($Response->data)) {
                            $Response->data = self::compress($Response->data);
                        }
                        if (!empty($Response->content)) {
                            $Response->content = self::compress($Response->content);
                        }
                    }
                }
            });
        }
    }

    /**
     * HTML compress function.
     *
     * @param $html
     * @return mixed
     */
    public static function compress($html)
    {
        $filters = [
            // remove javascript comments
            '/(?:<script[^>]*>|\G(?!\A))(?:[^\'"\/<]+|"(?:[^\\"]+|\\.)*"|\'(?:[^\\\']+|\\.)*\'|\/(?!\/)|<(?!\/script))*+\K\/\/[^\n|<]*/xsu' => '',
            // remove html comments except IE conditions
            '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/su' => '',
            // remove comments in the form /* */
            '/\/+?\s*\*[\s\S]*?\*\s*\/+/u' => '',
            // shorten multiple white spaces
            '/>\s{2,}</u' => '><',
            // shorten multiple white spaces
            '/\s{2,}/u' => ' ',
            // collapse new lines
            '/(\r?\n)/u' => '',
        ];

        $output = preg_replace(array_keys($filters), array_values($filters), $html);

        return $output;
    }
}