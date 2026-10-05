<?php

declare(strict_types=1);

namespace Test\App\Storage\Page;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Simpledynamic\Base\Model\BuilderInterface;
use stdClass;

/**
 * Билдер сущности "Страница"
 */
class PageBuilder implements BuilderInterface
{
    public function __construct(
        private readonly DatabaseManager $manager,
    ) {}

    /**
     * Найти страницу по id
     */
    public function find(int $pageId): ?stdClass
    {
        return $this->manager->query()->from('pages')->where('id', $pageId)->first();
    }

    /**
     * Найти страницы по id и/или user_id
     *
     * @param int[] $pageIds
     */
    public function findMany(array $pageIds = [], ?int $userId = null): Collection
    {
        if ($pageIds === [] && $userId === null) {
            return new Collection();
        }

        $query = $this->manager->query()->from('pages');

        if ($pageIds !== []) {
            $query = $query->whereIn('id', $pageIds);
        }

        if ($userId !== null) {
            $query = $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Создать страницу
     *
     * @param array{
     *     user_id:        int,
     *     page_id:        ?int,
     *     title:          string,
     *     description:    string|null,
     *     layout:         array<array-key, mixed>|string|null,
     *     early_dated_at?: string|null,
     *     late_dated_at?:  string|null,
     * } $page
     */
    public function create(array $page): int
    {
        return $this->manager
            ->query()
            ->from('pages')
            ->insertGetId([
                'user_id' => $page['user_id'],
                'page_id' => $page['page_id'],
                'title' => $page['title'],
                'description' => $page['description'],
                'layout' => $this->encodeLayout($page['layout']),
                'early_dated_at' => $page['early_dated_at'] ?? null,
                'late_dated_at' => $page['late_dated_at'] ?? null,
            ]);
    }

    /**
     * Изменить страницу
     *
     * @param array{
     *     user_id:        int,
     *     page_id:        ?int,
     *     title:          string,
     *     description:    string|null,
     *     layout:         array<array-key, mixed>|string|null,
     *     early_dated_at?: string|null,
     *     late_dated_at?:  string|null,
     * } $page
     */
    public function update(array $page): void
    {
        $this->manager
            ->query()
            ->from('pages')
            ->update([
                'user_id' => $page['user_id'],
                'page_id' => $page['page_id'],
                'title' => $page['title'],
                'description' => $page['description'],
                'layout' => $this->encodeLayout($page['layout']),
                'early_dated_at' => $page['early_dated_at'] ?? null,
                'late_dated_at' => $page['late_dated_at'] ?? null,
            ]);
    }

    /**
     * Привести layout к строке для jsonb-колонки
     *
     * @param array<array-key, mixed>|string|null $layout
     */
    private function encodeLayout(array|string|null $layout): ?string
    {
        if ($layout === null) {
            return null;
        }

        if (is_string($layout)) {
            return $layout;
        }

        $json = json_encode($layout);

        return is_string($json) ? $json : null;
    }

    /**
     * Удалить страницу
     *
     * @param array{id: int} $page
     */
    public function delete(array $page): void
    {
        $this->manager->query()->from('pages')->where('id', $page['id'])->delete();
    }
}
