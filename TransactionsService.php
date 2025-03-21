<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;

class TransactionsService
{
  public function __construct(private Database $db) {}

  public function selectCategoriesIncomes(): Database
  {
    $categories = $this->db->query(
      "SELECT name FROM incomes_category_default"
    );
    return $categories->fetchAllResults();
  }

  public function selectCategoriesPaymentMethods(): Database
  {
    $categories = $this->db->query(
      "SELECT name FROM payment_methods_default"
    );
    return $categories->fetchAllResults();
  }

  public function selectCategoriesExpenses(): Database
  {
    $categories = $this->db->query(
      "SELECT name FROM expenses_category_default"
    );
    return $categories->fetchAllResults();
  }

  public function createIncome($formData)
  {
    $formattedDate = "{$formData['date']} 00:00:00";
    $income_id = $this->db->query(
      "SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :category",
      [
        'user_id' => $_SESSION['user'],
        'category' => $formData['category']
      ]
    )->count();

    if (!$income_id) {
      die("Błąd: Nie znaleziono ID kategorii dla użytkownika {$_SESSION['user']} i kategorii {$formData['category']}");
    }
    $sql = "INSERT INTO incomes(user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
        VALUES(:user_id, :income_id, :amount, :date_of_income, :income_comment)";

    $params = [
      'user_id' => $_SESSION['user'],
      'income_id' => $income_id,
      'amount' => (float) str_replace(',', '.', $formData['price']),
      'date_of_income' => $formattedDate,
      'income_comment' => $formData['comment']
    ];
    $this->db->query($sql, $params);
  }

  public function createExpense($formData)
  {
    $formattedDate = "{$formData['date']} 00:00:00";
    $expense_id = $this->db->query(
      "SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :category",
      [
        'user_id' => $_SESSION['user'],
        'category' => $formData['category']
      ]
    )->count();
    if (!$expense_id) {
      die("Błąd: Nie znaleziono ID kategorii dla użytkownika {$_SESSION['user']} i kategorii {$formData['category']}");
    }

    $payment_method_id = $this->db->query(
      "SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :paymentMethod",
      [
        'user_id' => $_SESSION['user'],
        'paymentMethod' => $formData['paymentMethod']
      ]
    )->count();
    if (!$payment_method_id) {
      die("Błąd: Nie znaleziono ID metody płatności dla użytkownika {$_SESSION['user']} i kategorii {$formData['paymentMethod']}");
    }

    $sql = "INSERT INTO expenses(user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
        VALUES(:user_id, :expense_id, :payment_method_id, :amount, :date_of_expense, :expense_comment)";

    $params = [
      'user_id' => $_SESSION['user'],
      'expense_id' => $expense_id,
      'payment_method_id' => $payment_method_id,
      'amount' => (float) str_replace(',', '.', $formData['price']),
      'date_of_expense' => $formattedDate,
      'expense_comment' => $formData['comment']
    ];
    $this->db->query($sql, $params);
  }

  public function getUserIncomes(int $length, int $offset)
  {
    $searchTerm = addcslashes($_GET['s'] ?? '', '%_');
    $params = [
      'user_id' => $_SESSION['user'],
      'name' => "%{$searchTerm}%"
    ];

    $incomes = $this->db->query(
      "SELECT incomes.amount, incomes.date_of_income, incomes_category_assigned_to_users.name, DATE_FORMAT(date_of_income, '%Y-%m-%d') as formatted_date
       FROM incomes
       JOIN incomes_category_assigned_to_users ON incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
       WHERE incomes.user_id = :user_id
       AND name LIKE :name
       ORDER BY incomes.amount DESC
       LIMIT {$length} OFFSET {$offset}",
      $params
    )->findAll();

    $incomesCount = $this->db->query(
      "SELECT COUNT(*)
       FROM incomes
       JOIN incomes_category_assigned_to_users ON incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
       WHERE incomes.user_id = :user_id
       AND name LIKE :name",
      $params
    )->count();
    return [$incomes, $incomesCount];
  }

  public function getUserExpenses(int $length, int $offset)
  {
    $searchTerm = addcslashes($_GET['s'] ?? '', '%_');
    $params = [
      'user_id' => $_SESSION['user'],
      'name' => "%{$searchTerm}%"
    ];

    $expenses = $this->db->query(
      "SELECT expenses.amount, expenses.date_of_expense, expenses_category_assigned_to_users.name, DATE_FORMAT(date_of_expense, '%Y-%m-%d') as formatted_date
       FROM expenses
       JOIN expenses_category_assigned_to_users ON expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
       WHERE expenses.user_id = :user_id
       AND name LIKE :name
       ORDER BY expenses.amount DESC
       LIMIT {$length} OFFSET {$offset}",
      $params
    )->findAll();

    $expensesCount = $this->db->query(
      "SELECT COUNT(*)
      FROM expenses
       JOIN expenses_category_assigned_to_users ON expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
       WHERE expenses.user_id = :user_id
       AND name LIKE :name",
      $params
    )->count();
    return [$expenses, $expensesCount];
  }

  public function sumIncomes($userId)
  {
    $result = $this->db->query(
      "SELECT SUM(incomes.amount) AS total FROM incomes
      WHERE incomes.user_id = :user_id",
      [
        'user_id' => $userId
      ]
    )->find();

    return $result ? (float) $result['total'] : 0;
  }

  public function sumExpenses($userId)
  {
    $result = $this->db->query(
      "SELECT SUM(expenses.amount) AS total FROM expenses
      WHERE expenses.user_id = :user_id",
      [
        'user_id' => $userId
      ]
    )->find();

    return $result ? (float) $result['total'] : 0;
  }

  public function getUserIncomesByPeriod($startDay, $endDay, $userId, $length, $offset)
  {
    $incomesPeriod = $this->db->query(
      "SELECT incomes.amount, incomes.date_of_income, incomes_category_assigned_to_users.name, DATE_FORMAT(date_of_income, '%Y-%m-%d') as formatted_date FROM incomes 
      JOIN incomes_category_assigned_to_users ON incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
       WHERE incomes.user_id = :user_id
       AND incomes.date_of_income BETWEEN :startDay and :endDay
       ORDER BY incomes.amount DESC
       LIMIT {$length} OFFSET {$offset}",
      [
        'startDay' => $startDay,
        'endDay' => $endDay,
        'user_id' => $userId
      ]
    )->findAll();

    return $incomesPeriod;
  }
}
