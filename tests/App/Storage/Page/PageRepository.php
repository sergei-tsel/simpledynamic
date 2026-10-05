<?php

declare(strict_types=1);

namespace Test\App\Storage\Page;

use Illuminate\Support\Collection;
use Simpledynamic\Base\Model\RepositoryInterface;
use stdClass;

/**
 * Репозиторий сущности "Страница"
 */
readonly class PageRepository implements RepositoryInterface
{
    public function __construct(
        private PageBuilder $builder,
    ) {}

    /**
     * Найти страницу по id
     */
    public function findById(int $pageId): ?PageModel
    {
        $data = $this->builder->find(pageId: $pageId);

        if ($data === null) {
            return null;
        }

        return new PageModel(attributes: $data);
    }

    /**
     * Найти страницы по id
     *
     * @param int[] $pageIds
     */
    public function findManyByIds(array $pageIds): Collection
    {
        $data = $this->builder->findMany(pageIds: $pageIds);

        if ($data->count() > 0) {
            $data->transform(static fn(stdClass $item): PageModel => new PageModel(attributes: $item));
        }

        return $data;
    }

    /**
     * Получить список страниц по id пользователя
     */
    public function getListByUserId(int $userId): Collection
    {
        $data = $this->builder->findMany(userId: $userId);

        if ($data->count() > 0) {
            $data->transform(static fn(stdClass $item): PageModel => new PageModel(attributes: $item));
        }

        return $data;
    }

    /**
     * Создать страницу
     */
    public function create(PageModel $page): ?PageModel
    {
        $modelId = $this->builder->create(page: [
            'user_id' => $page->userId,
            'page_id' => $page->pageId,
            'title' => $page->title,
            'description' => $page->description,
            'layout' => $page->layout,
            'early_dated_at' => $page->earlyDatedAt?->toDateTimeString(),
            'late_dated_at' => $page->lateDatedAt?->toDateTimeString(),
        ]);

        return $this->findById(pageId: $modelId);
    }

    /**
     * Изменить страницу
     */
    public function update(PageModel $page): ?PageModel
    {
        if ($page->id === null) {
            return null;
        }

        $this->builder->update(page: [
            'user_id' => $page->userId,
            'page_id' => $page->pageId,
            'title' => $page->title,
            'description' => $page->description,
            'layout' => $page->layout,
            'early_dated_at' => $page->earlyDatedAt?->toDateTimeString(),
            'late_dated_at' => $page->lateDatedAt?->toDateTimeString(),
        ]);

        return $this->findById(pageId: $page->id);
    }

    /**
     * Удалить страницу
     */
    public function delete(PageModel $page): void
    {
        if ($page->id === null) {
            return;
        }

        $this->builder->delete(page: [
            'id' => $page->id,
        ]);
    }
}
