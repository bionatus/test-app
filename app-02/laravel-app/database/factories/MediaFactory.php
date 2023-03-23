<?php

namespace Database\Factories;

use App\Constants\MediaCollectionNames;
use App\Models\Comment;
use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\Order;
use App\Models\Post;
use App\Models\Subject;
use App\Models\SupplyCategory;
use Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Media create($attributes = [], ?Model $parent = null)
 * @method Collection|Media make($attributes = [], ?Model $parent = null)
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'model_type'            => Post::MORPH_ALIAS,
            'model_id'              => 1,
            'uuid'                  => $this->faker->uuid,
            'collection_name'       => MediaCollectionNames::IMAGES,
            'name'                  => 100,
            'file_name'             => '100.jpeg',
            'mime_type'             => 'image/jpeg',
            'disk'                  => Config::get('media-library.disk_name'),
            'conversions_disk'      => Config::get('media-library.disk_name'),
            'size'                  => 2497,
            'manipulations'         => [],
            'custom_properties'     => [],
            'generated_conversions' => [],
            'responsive_images'     => [],
            'order_column'          => 1,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
        ];
    }

    public function usingPost(Post $post): self
    {
        return $this->state(function() use ($post) {
            return [
                'model_type' => Post::MORPH_ALIAS,
                'model_id'   => $post->getKey(),
            ];
        });
    }

    public function usingComment(Comment $comment): self
    {
        return $this->state(function() use ($comment) {
            return [
                'model_type' => Comment::MORPH_ALIAS,
                'model_id'   => $comment->getKey(),
            ];
        });
    }

    public function usingTag(IsTaggable $taggable): self
    {
        return $this->state(function() use ($taggable) {
            return [
                'model_type' => $taggable->getMorphClass(),
                'model_id'   => $taggable->getKey(),
            ];
        });
    }

    public function usingSubject(Subject $subject): self
    {
        return $this->state(function() use ($subject) {
            return [
                'model_type' => Subject::MORPH_ALIAS,
                'model_id'   => $subject->getKey(),
            ];
        });
    }

    public function usingSupplyCategory(SupplyCategory $supplyCategory): self
    {
        return $this->state(function() use ($supplyCategory) {
            return [
                'model_type' => SupplyCategory::MORPH_ALIAS,
                'model_id'   => $supplyCategory->getKey(),
            ];
        });
    }

    public function post(): self
    {
        return $this->state(function() {
            return [
                'model_type'      => Post::MORPH_ALIAS,
                'collection_name' => MediaCollectionNames::IMAGES,
            ];
        });
    }

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'model_type'      => Order::MORPH_ALIAS,
                'model_id'        => $order->getKey(),
                'collection_name' => MediaCollectionNames::INVOICE,
            ];
        });
    }
}
