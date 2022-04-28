<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Exception;
use App\Traits\StatusTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use StatusTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    public function paymentOrder(Request $request)
    {
        $orderTypes = $this->getStatuses('order')->keyBy('name');
        $transactionTypes = $this->getStatuses('transaction')->keyBy('name');

        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                'numeric',
                Rule::exists('orders', 'id')->where(function ($query) use ($orderTypes) {
                    $query->where('status_id', $orderTypes['pending']['id']);
                }),
            ],
            'payment_method_id' => 'required|numeric|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
          return response()->json($validator->errors(), $this->unprocessableStatus);
        }

        $result = [
            'success' => true,
            'message' => null,
            'data' => null
        ];

        DB::beginTransaction();

        try {


            $order = Order::find($request['order_id']);
            $order->status_id = $orderTypes['completed']['id'];
            $order->save();

            $transaction = Transaction::where('order_id', $request['order_id'])
            ->where('status_id', $transactionTypes['pending']['id'])
            ->update([
                'payment_method_id' => $request['payment_method_id'],
                'status_id' => $transactionTypes['paid']['id']
            ]);

            $result['data'] = $order;

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $result['success'] = false;
            $result['message'] = $e->getMessage();

            return response()->json($result, $this->unprocessableStatus);
        }

        $result['message'] = 'checkout payment successfully.';
        return response()->json($result, $this->successStatus);
    }

    public function cancelOrder(Request $request)
    {
        $orderTypes = $this->getStatuses('order')->keyBy('name');
        $transactionTypes = $this->getStatuses('transaction')->keyBy('name');


        $validator = Validator::make($request->all(), [
            // 'order_id' => 'required|numeric|exists:orders,id',
            'order_id' => [
                'required',
                'numeric',
                Rule::exists('orders', 'id')->where(function ($query) use ($orderTypes) {
                    return $query->whereIn('status_id', [$orderTypes['completed']['id'], $orderTypes['pending']['id']]);
                }),
            ],
        ]);

        if ($validator->fails()) {
          return response()->json($validator->errors(), $this->unprocessableStatus);
        }

        $result = [
            'success' => true,
            'message' => null,
            'data' => null
        ];

        DB::beginTransaction();

        try {
            $order = Order::find($request['order_id']);

            if ($order->status_id == $orderTypes['completed']['id']) {
                $order->status_id = $orderTypes['refunded']['id'];
            } else {
                $order->status_id = $orderTypes['cancelled']['id'];
            }

            $order->save();


            $transaction = Transaction::where('order_id', $request['order_id'])
            ->where('status_id', $transactionTypes['paid']['id'])
            ->first();

            $cancelOrder = [
                'order_id' => $order['id'],
                'credit' => $order['total_amount'],
                'debit' => 0,
                'status_id' => $transactionTypes['cancelled']['id']
            ];

            $result['message'] = 'cancel order successfully.';

            if (!empty($transaction)) {
                $cancelOrder['payment_method_id'] = $transaction['payment_method_id'];

                if ($transaction['status_id'] == $transactionTypes['paid']['id']) {
                    $cancelOrder['status_id'] = $transactionTypes['refunded']['id'];
                    $result['message'] = 'refund order successfully.';
                } else {
                    $cancelOrder['status_id'] = $transactionTypes['cancelled']['id'];
                }
            }

            Transaction::create($cancelOrder);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $result['success'] = false;
            $result['message'] = $e->getMessage();

            return response()->json($result, $this->unprocessableStatus);
        }

        return response()->json($result, $this->successStatus);
    }
}
