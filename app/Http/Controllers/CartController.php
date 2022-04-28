<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
// use App\Http\Requests\Request as FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Exception;
use App\Traits\StatusTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CartController extends Controller
{
    use StatusTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [
            'status' => true
        ];
        return response()->json($result, $this->successStatus);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function show(cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, cart $cart)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'quantity' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
          return response()->json($validator->errors(), $this->unprocessableStatus);
        }

        $result = [
            'success' => true,
            'message' => null,
            'data' => null
        ];

        $product = Product::select(['id', 'price'])
        ->find($request['product_id']);

        $carts = Cart::select(['product_id', 'price', 'quantity', 'amount'])
        ->get()
        ->keyBy('product_id')
        ->toArray();

        $currentProduct = $carts[$product['id']] ?? [];

        $currentProduct = array_merge($currentProduct, [
            'price' => $product['price'],
            'quantity' => $request['quantity']
        ]);

        $currentProduct['amount'] = $currentProduct['price'] * $currentProduct['quantity'];

        $carts[$product['id']] = $currentProduct;

        $data['product_id'] = $product['id'];
        $data['price'] = $product['price'];
        $data['quantity'] = $request['quantity'];
        $data['amount'] = $currentProduct['amount'];
        $data['tax'] = config('constant.tax');
        $data['service_charge'] = config('constant.service_charge');
        $data['calculated_amount'] = collect($carts)->sum('amount');
        $data['payable_amount'] = $this->getPayableAmount($data['calculated_amount']);
        $result['data'] = $data;

        Cart::updateOrCreate(
            ['product_id' => $product['id']],
            Arr::except($carts[$product['id']], 'product_id')
        );

        return response()->json($result, $this->successStatus);
    }

    private function getPayableAmount($amount)
    {
        $tax = config('constant.tax');
        $serviceCharge = config('constant.service_charge');

        return round($amount * (($tax + $serviceCharge) + 100) / 100);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(cart $cart)
    {
        //
    }

    public function checkout(Request $request)
    {
        $result = [
            'success' => true,
            'message' => null,
            'data' => null
        ];

        DB::beginTransaction();

        try {
            $statuses = $this->getStatuses('order')->keyBy('name');
            $transactionTypes = $this->getStatuses('transaction')->keyBy('name');

            $carts = Cart::get();

            $calculateAmount = $carts->sum('amount');
            $payableAmount = $this->getPayableAmount($calculateAmount);

            $order = Order::create([
                'order_no' => md5(rand().uniqid()),
                'calculated_amount' => $calculateAmount,
                'tax' => config('constant.tax'),
                'service_charge' => config('constant.service_charge'),
                'total_amount' => $payableAmount,
                'status_id' => $statuses['pending']['id'],
            ]);

            $orderItems = [];
            foreach ($carts as $key => $cart) {
                if ($cart['quantity'] <= 0) {
                    continue;
                }

                $orderItems[] = [
                    'order_id' => $order['id'],
                    'product_id' => $cart['product_id'],
                    'unit_price' => $cart['price'],
                    'quantity' => $cart['quantity'],
                    'total_amount' => $cart['amount'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if (!empty($orderItems)) {
                OrderItem::insert($orderItems);
            }

            Transaction::create([
                'order_id' => $order['id'],
                'debit' => $payableAmount,
                'status_id' => $transactionTypes['pending']['id'],
            ]);

            Cart::whereIn('id', array_column($carts->toarray(), 'id'))->delete();

            $result['data'] = $order;

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            $result['success'] = false;
            $result['message'] = $e->getMessage();

            return response()->json($result, $this->unprocessableStatus);
        }

        $result['message'] = 'checkout successfully and pending for payment.';
        return response()->json($result, $this->successStatus);
    }
}
