<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class CartController extends Controller
{
    private const TAX_RATE = 12.5; // prices are INCLUSIVE of 12.5% tax

    private array $catalog = [
        1 => ['id' => 1, 'name' => 'Item 1', 'price' => 10.00, 'description' => 'Grilled Chicken'],
        2 => ['id' => 2, 'name' => 'Item 2', 'price' => 7.50,  'description' => 'Fish & Chips'],
        3 => ['id' => 3, 'name' => 'Item 3', 'price' => 5.00,  'description' => 'Caesar Salad'],
        4 => ['id' => 4, 'name' => 'Item 4', 'price' => 2.50,  'description' => 'Garlic Bread'],
        5 => ['id' => 5, 'name' => 'Item 5', 'price' => 3.00,  'description' => 'Soft Drink'],
    ];

    public function index(): string
    {
        return view('cart/index', [
            'catalog'  => $this->catalog,
            'tax_rate' => self::TAX_RATE,
        ]);
    }

    public function data(): ResponseInterface
    {
        $cart = $this->getCart();
        return $this->response->setJSON([
            'success' => true,
            'cart'    => array_values($cart),
            'totals'  => $this->calcTotals($cart),
        ]);
    }

    public function add(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('AJAX only', 400);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $qty    = max(1, (int) ($this->request->getPost('qty') ?? 1));

        if (!isset($this->catalog[$itemId])) {
            return $this->jsonError('Item not found', 404);
        }

        $cart = $this->getCart();

        if (isset($cart[$itemId])) {
            $cart[$itemId]['qty'] += $qty;
            $cart[$itemId]['line_total'] = round($cart[$itemId]['price'] * $cart[$itemId]['qty'], 2);
        } else {
            $cart[$itemId] = [
                'item_id'    => $itemId,
                'name'       => $this->catalog[$itemId]['name'],
                'price'      => $this->catalog[$itemId]['price'],
                'qty'        => $qty,
                'line_total' => round($this->catalog[$itemId]['price'] * $qty, 2),
            ];
        }

        $this->saveCart($cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => array_values($cart),
            'totals'  => $this->calcTotals($cart),
        ]);
    }

    public function update(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('AJAX only', 400);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $qty    = (int) $this->request->getPost('qty');

        $cart = $this->getCart();

        if ($qty <= 0) {
            unset($cart[$itemId]);
        } elseif (isset($cart[$itemId])) {
            $cart[$itemId]['qty']        = $qty;
            $cart[$itemId]['line_total'] = round($cart[$itemId]['price'] * $qty, 2);
        }

        $this->saveCart($cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => array_values($cart),
            'totals'  => $this->calcTotals($cart),
        ]);
    }

    public function remove(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('AJAX only', 400);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $cart   = $this->getCart();
        unset($cart[$itemId]);
        $this->saveCart($cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => array_values($cart),
            'totals'  => $this->calcTotals($cart),
        ]);
    }

    public function clear(): ResponseInterface
    {
        $this->saveCart([]);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => [],
            'totals'  => $this->calcTotals([]),
        ]);
    }

    private function getCart(): array
    {
        return session()->get('cart') ?? [];
    }

    private function saveCart(array $cart): void
    {
        session()->set('cart', $cart);
    }

    /**
     * Prices are INCLUSIVE of 12.5% tax.
     * Tax portion = Total × (rate / (100 + rate))
     */
    private function calcTotals(array $cart): array
    {
        $totalInclTax = 0.0;
        foreach ($cart as $item) {
            $totalInclTax += $item['price'] * $item['qty'];
        }

        $rate    = self::TAX_RATE;
        $tax     = round($totalInclTax * ($rate / (100 + $rate)), 2);
        $net     = round($totalInclTax - $tax, 2);
        $items   = array_sum(array_column($cart, 'qty'));

        return [
            'total_incl_tax' => round($totalInclTax, 2),
            'tax'            => $tax,
            'net'            => $net,
            'tax_rate'       => $rate,
            'item_count'     => $items,
        ];
    }

    private function jsonError(string $msg, int $code = 400): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON(['success' => false, 'error' => $msg]);
    }
}