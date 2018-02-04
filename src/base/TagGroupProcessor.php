<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

use Craft;
use craft\elements\Tag;
use craft\models\FieldLayout;
use craft\models\TagGroup;
use pennebaker\architect\Architect;

/**
 * TagGroupProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class TagGroupProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        if (sizeof($item['fieldLayout']) > 1 || (sizeof($item['fieldLayout']) === 1 && !isset($item['fieldLayout']['Content']))) {
            $errors = [
                'fieldLayout' => [
                    Architect::t('Field layout can only have 1 tab named "Content".')

                ]
            ];
            return [null, $errors];
        }

        $tagGroup = new TagGroup([
            'name' => $item['name'],
            'handle' => $item['handle'],
        ]);

        $fieldLayout = new FieldLayout();
        $fieldLayout->type = Tag::class;

        return [$tagGroup, null];
    }

    /**
     * @param mixed $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->tags->saveTagGroup($item);
    }

    /**
     * @param array $item
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function setFieldLayout($item) {
        $tagGroup = Craft::$app->tags->getTagGroupByHandle($item['handle']);

        $fieldLayout = $this->createFieldLayout($item, Tag::class);
        $tagGroup->setFieldLayout($fieldLayout);

        return $this->save($tagGroup);
    }
}