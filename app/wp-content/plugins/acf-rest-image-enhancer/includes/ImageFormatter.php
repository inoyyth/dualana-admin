<?php

if (!defined('ABSPATH')) {
    exit;
}

class ACF_REST_Image_Formatter
{
    public static function format($value, $field)
    {
        if (!$value) {
            return $value;
        }

        switch ($field['type']) {

            case 'image':
                return self::image($value);

            case 'gallery':
                return array_map(function ($img) {
                    return self::image($img);
                }, $value);

            case 'group':
                return self::walk($value, $field['sub_fields']);

            case 'repeater':
                foreach ($value as &$row) {
                    $row = self::walk($row, $field['sub_fields']);
                }
                return $value;

            case 'flexible_content':
                foreach ($value as &$layout) {

                    foreach ($field['layouts'] as $layoutDef) {

                        if ($layoutDef['name'] !== $layout['acf_fc_layout']) {
                            continue;
                        }

                        $layout = self::walk(
                            $layout,
                            $layoutDef['sub_fields']
                        );
                    }

                }

                return $value;

            default:
                return $value;
        }
    }

    private static function walk($data, $fields)
    {
        foreach ($fields as $sub) {

            $name = $sub['name'];

            if (!isset($data[$name])) {
                continue;
            }

            $data[$name] = self::format(
                $data[$name],
                $sub
            );
        }

        return $data;
    }

    private static function image($image)
    {
        $id = is_array($image)
            ? ($image['ID'] ?? $image['id'] ?? 0)
            : $image;

        if (!$id) {
            return null;
        }

        $meta = wp_get_attachment_metadata($id);

        return [

            'id' => $id,

            'title' => get_the_title($id),

            'alt' => get_post_meta(
                $id,
                '_wp_attachment_image_alt',
                true
            ),

            'caption' => wp_get_attachment_caption($id),

            'description' => get_post($id)->post_content ?? '',

            'filename' => basename(
                get_attached_file($id)
            ),

            'filesize' => filesize(
                get_attached_file($id)
            ),

            'mime_type' => get_post_mime_type($id),

            'url' => wp_get_attachment_url($id),

            'srcset' => wp_get_attachment_image_srcset($id),

            'width' => $meta['width'] ?? null,

            'height' => $meta['height'] ?? null,

            'sizes' => self::sizes($id, $meta),

            'metadata' => $meta,

        ];
    }

    private static function sizes($id, $meta)
    {
        $sizes = [];

        foreach (get_intermediate_image_sizes() as $size) {

            $src = wp_get_attachment_image_src(
                $id,
                $size
            );

            if (!$src) {
                continue;
            }

            $sizes[$size] = [

                'url' => $src[0],
                'width' => $src[1],
                'height' => $src[2],

            ];
        }

        return $sizes;
    }
}