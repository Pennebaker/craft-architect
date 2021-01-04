<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

use pennebaker\architect\Architect;

use Craft;
use craft\elements\Tag;
use craft\models\FieldLayout;
use craft\models\TagGroup;

/**
 * TagGroupProcessor
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
    public function parse(array $item): array
    {
        if (\count($item['fieldLayout']) > 1) {
            $errors = [
                'fieldLayout' => [
                    Architect::t('Field layout can only have 1 tab.')

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
    public function setFieldLayout($item)
    {
        $tagGroup = Craft::$app->tags->getTagGroupByHandle($item['handle']);

        if ($tagGroup) {
            $fieldLayout = $this->createFieldLayout($item, Tag::class);
            $tagGroup->setFieldLayout($fieldLayout);

            return $this->save($tagGroup);
        }
        return false;
    }

    /**
     * @param mixed $item
     * @param array $extraAttributes
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var TagGroup $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $tagGroupObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        return $this->stripNulls($tagGroupObj);
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function exportById($id): array
    {
        $tagGroup = Craft::$app->tags->getTagGroupById((int) $id);

        return $this->export($tagGroup);
    }

    /**
     * Gets an object from the passed in UID for export.
     *
     * @param $uid
     *
     * @return mixed
     */
    public function exportByUid($uid)
    {
        // TODO: Implement exportByUid() method.
    }
}