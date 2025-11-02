<?php

namespace App\Services\UI\DataTable;

use App\Models\User;

/**
 * Users Table Model
 * 
 * Implementation for real User model from database
 */
class UsersTableModel extends AbstractDataTableModel
{
    /**
     * Get all users data from database
     * 
     * @return array
     */
    protected function getAllData(): array
    {
        return User::all()->toArray();
    }

    /**
     * Get table columns definition
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return [
            'id' => ['label' => 'ID', 'width' => [60, 80]],
            'name' => ['label' => 'Name', 'width' => [200, 300]],
            'email' => ['label' => 'Email', 'width' => [250, 350]],
            'actions' => ['label' => 'Actions', 'width' => [100, 150]],
        ];
    }

    /**
     * Get formatted data for table display
     * 
     * @return array
     */
    public function getFormattedPageData(int $currentPage, int $perPage): array
    {
        $users = $this->getPageData($currentPage, $perPage);
        $formatted = [];

        foreach ($users as $index => $user) {
            // rowIndex is the visual row index in the table (0-based within current page)
            $rowIndex = $index;

            $formatted[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'actions' => [
                    'button' => [
                        'label' => "Edit",
                        'action' => 'edit_user',
                        'style' => 'primary',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex,
                        ]
                    ]
                ],
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
        $user = User::find($userId);
        return $user ? $user->toArray() : null;
    }

    /**
     * Update user data in database
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Only update allowed fields
        $allowedFields = ['name', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return false;
        }

        return $user->update($updateData);
    }

    /**
     * Delete user from database
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
