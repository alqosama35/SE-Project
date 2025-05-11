<?php

namespace App\Models;

class Shopping extends Model implements Payable {
    protected static string $table = 'shopping';
    
    protected array $fillable = [
        'id',
        'item_name',
        'price',
        'quantity',
        'payment_id'
    ];
    
    protected array $relationships = [
        'payment' => Payment::class
    ];

    private const VALID_STATUSES = ['IN_CART', 'PURCHASED', 'REMOVED'];

    public function addToCart(Visitor $visitor): void {
        try {
            if ($this->getAttribute('status') !== null) {
                throw new \RuntimeException("Item is already in a cart");
            }

            $shoppingItem = new ShoppingItem([
                'name' => $this->getAttribute('item_name'),
                'price' => $this->getAttribute('price'),
                'quantity' => $this->getAttribute('quantity'),
                'status' => 'IN_CART'
            ]);
            $shoppingItem->setVisitor($visitor);
            $shoppingItem->save();
        } catch (\Exception $e) {
            error_log("Error adding item to cart {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeFromCart(Visitor $visitor): void {
        try {
            $items = ShoppingItem::findInCart($visitor->getId());
            foreach ($items as $item) {
                if ($item->getName() === $this->getAttribute('item_name')) {
                    $item->setStatus('REMOVED');
                    $item->save();
                    break;
                }
            }
        } catch (\Exception $e) {
            error_log("Error removing item from cart {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function purchase(Visitor $visitor): Receipt {
        try {
            $payment = new Payment([
                'amount' => $this->getTotal(),
                'method' => 'CARD',
                'status' => 'PENDING'
            ]);
            
            $this->processPayment($payment);
            
            $items = ShoppingItem::findInCart($visitor->getId());
            foreach ($items as $item) {
                if ($item->getName() === $this->getAttribute('item_name')) {
                    $item->setStatus('PURCHASED');
                    $item->save();
                }
            }

            return new Receipt([
                'amount' => $this->getTotal(),
                'description' => $this->getDescription(),
                'payment_id' => $payment->getId()
            ]);
        } catch (\Exception $e) {
            error_log("Error purchasing item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTotal(): float {
        return $this->getAttribute('price') * $this->getAttribute('quantity');
    }

    public function getDescription(): string {
        return "Purchase of {$this->getAttribute('quantity')}x {$this->getAttribute('item_name')}";
    }

    // Payable interface methods
    public function getId(): ?int {
        return (int)substr($this->getAttribute('id'), 9); // Remove 'shopping_' prefix
    }

    public function getAmount(): float {
        return $this->getTotal();
    }

    public function processPayment(Payment $payment): void {
        try {
            $payment->setAmount($this->getAmount());
            $payment->process();
            $this->setAttribute('payment_id', $payment->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error processing payment for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function refundPayment(Payment $payment): void {
        try {
            if ($this->getAttribute('payment_id') !== $payment->getId()) {
                throw new \InvalidArgumentException("Invalid payment for refund");
            }
            $payment->refund();
        } catch (\Exception $e) {
            error_log("Error refunding payment for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid shopping status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding shopping items by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }
} 