<?php

use Tests\TestCase;
use App\Services\UI\DataTable\UsersDataTableModel;

class TableDemoServiceTest extends TestCase
{
    /** @test */
    public function it_can_create_users_data_table_model()
    {
        $model = new UsersDataTableModel(5, 1);
        
        $this->assertEquals(5, $model->getPerPage());
        $this->assertEquals(1, $model->getCurrentPage());
        $this->assertGreaterThan(0, $model->getTotalItems());
        $this->assertGreaterThan(0, $model->getTotalPages());
    }

    /** @test */
    public function it_can_get_paginated_data()
    {
        $model = new UsersDataTableModel(5, 1);
        
        $pageData = $model->getPageData();
        $this->assertCount(5, $pageData);
        
        $formattedData = $model->getFormattedPageData();
        $this->assertCount(5, $formattedData);
        
        // Check first user structure
        $firstUser = $formattedData[0];
        $this->assertArrayHasKey('id', $firstUser);
        $this->assertArrayHasKey('name', $firstUser);
        $this->assertArrayHasKey('country', $firstUser);
        $this->assertArrayHasKey('actions', $firstUser);
        $this->assertArrayHasKey('remove', $firstUser);
    }

    /** @test */
    public function it_can_navigate_pages()
    {
        $model = new UsersDataTableModel(5, 1);
        
        $this->assertTrue($model->hasNextPage());
        $this->assertFalse($model->hasPreviousPage());
        
        // Go to page 2
        $model->setCurrentPage(2);
        $this->assertEquals(2, $model->getCurrentPage());
        $this->assertTrue($model->hasPreviousPage());
        
        // Get different data on page 2
        $page2Data = $model->getPageData();
        $this->assertCount(5, $page2Data);
        
        // Verify it's different from page 1
        $model->setCurrentPage(1);
        $page1Data = $model->getPageData();
        $this->assertNotEquals($page1Data, $page2Data);
    }

    /** @test */
    public function it_provides_pagination_info()
    {
        $model = new UsersDataTableModel(5, 2);
        
        $info = $model->getPaginationInfo();
        
        $this->assertEquals(2, $info['current_page']);
        $this->assertEquals(5, $info['per_page']);
        $this->assertArrayHasKey('total_items', $info);
        $this->assertArrayHasKey('total_pages', $info);
        $this->assertArrayHasKey('has_next', $info);
        $this->assertArrayHasKey('has_previous', $info);
        $this->assertEquals(6, $info['from']); // Page 2, items 6-10
        $this->assertEquals(10, $info['to']);
    }

    /** @test */
    public function it_can_find_user_by_id()
    {
        $model = new UsersDataTableModel();
        
        $user = $model->findUserById(1);
        $this->assertNotNull($user);
        $this->assertEquals(1, $user['id']);
        $this->assertEquals('Alice Johnson', $user['name']);
        
        $nonExistentUser = $model->findUserById(999);
        $this->assertNull($nonExistentUser);
    }

    /** @test */
    public function it_has_proper_column_definitions()
    {
        $model = new UsersDataTableModel();
        
        $columns = $model->getColumns();
        
        $this->assertArrayHasKey('id', $columns);
        $this->assertArrayHasKey('name', $columns);
        $this->assertArrayHasKey('country', $columns);
        $this->assertArrayHasKey('actions', $columns);
        $this->assertArrayHasKey('remove', $columns);
        
        // Check column structure
        $idColumn = $columns['id'];
        $this->assertEquals('Id', $idColumn['label']);
        $this->assertArrayHasKey('width', $idColumn);
        $this->assertIsArray($idColumn['width']);
        $this->assertCount(2, $idColumn['width']); // [min, max]
    }
}