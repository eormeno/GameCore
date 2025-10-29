<?php

namespace App\Services\UI\DataTable;

/**
 * Users Data Table Model
 * 
 * Implementation for the users demo data from users_data.php
 */
class UsersDataTableModel extends AbstractDataTableModel
{
    private ?array $usersData = null;

    /**
     * Get all users data from file
     * 
     * @return array
     */
    protected function getAllData(): array
    {
        if ($this->usersData === null) {
            $this->usersData = require app_path('Data/users_data.php');
        }
        return $this->usersData;
    }

    /**
     * Get table columns definition
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return [
            'id' => ['label' => 'Id', 'width' => [50, 80]],
            'name' => ['label' => 'Name', 'width' => [200, 250]],
            'country' => ['label' => 'Country', 'width' => [200, 250]],
            'actions' => ['label' => 'Actions', 'width' => [80, 120]],
            'remove' => ['label' => '', 'width' => [80, 120]]
        ];
    }

    /**
     * Get formatted data for table display
     * 
     * @return array
     */
    public function getFormattedPageData(): array
    {
        $users = $this->getPageData();
        $formatted = [];

        foreach ($users as $index => $user) {
            $rowIndex = (($this->currentPage - 1) * $this->perPage) + $index;
            
            $formatted[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'country' => $user['country'],
                'actions' => [
                    'button' => [
                        'label' => "Edit #{$user['id']}",
                        'action' => 'edit_user',
                        'style' => 'primary',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex,
                            'name' => $user['name']
                        ]
                    ]
                ],
                'remove' => [
                    'button' => [
                        'label' => "Remove #{$user['id']}",
                        'action' => 'remove_user',
                        'style' => 'danger',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex
                        ]
                    ]
                ]
            ];
        }

        return $formatted;
    }

    /**
     * Find user by ID
     * 
     * @param int $userId
     * @return array|null
     */
    public function findUserById(int $userId): ?array
    {
        $users = $this->getAllData();
        foreach ($users as $user) {
            if ($user['id'] == $userId) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Update user data (for demo purposes)
     * In a real implementation, this would update the database
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool
    {
        // For demo purposes, we just return true
        // In a real implementation, this would update the database
        return true;
    }

    /**
     * Remove user (for demo purposes)
     * In a real implementation, this would delete from database
     * 
     * @param int $userId
     * @return bool
     */
    public function removeUser(int $userId): bool
    {
        // For demo purposes, we just return true
        // In a real implementation, this would delete from database
        return true;
    }
}