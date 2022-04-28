<template>
  <div>
    <div class="container">
      <section id="product-cart"></section>
    </div>

    <header id="site-header" style="margin: 0px 15px 30px 15px; text-align:center; margin:auto; text-align:center; width:65%;">
      <div class="container w-auto">
        <h1>Shopping cart</h1>
      </div>
    </header>

    <div id="user-cart" class="container">
      <!-- <section id="cart">
        <article class="product">
          <header>
            <a class="removeoff">
              <img
                src="http://www.astudio.si/preview/blockedwp/wp-content/uploads/2012/08/1.jpg"
                alt=""
              />
            </a>
          </header>

          <div class="content">
            <h1>Lorem ipsum</h1>
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Soluta, numquam quis
            perspiciatis ea ad omnis provident laborum dolore in atque.
          </div>

          <footer class="content">
            <span class="qt-minus">-</span>
            <span class="qt">2</span>
            <span class="qt-plus">+</span>

            <h2 class="full-price">29.98€</h2>

            <h2 class="price">14.99€</h2>
          </footer>
        </article>
      </section>
       -->
    </div>

    <footer id="site-footer" style="padding:30px 15px;">
        <div class="container clearfix" style="width:auto; margin-right:0;">
            <div class="d-inline-block" style="margin-right:10px;">

                <a v-if="!this.paymentSuccess" class="btn d-inline-block" @click="cancel">Cancel</a>
                <a v-else class="btn d-inline-block" @click="cancel">Refund</a>
            </div>
            <div class="d-inline-block">
                <div class="tax">
                    Tax: <span>-</span>
                </div>
                <div class="service_charge" >
                    Service Charge: <span>-</span>
                </div>
                <div class="subtotal" style="float:none; width:auto;">
                    Sub Total: <span>0</span>
                </div>
                <div class="total">
                    Total: <span>0</span>
                </div>
                <a class="btn d-inline-block" @click="checkout">Checkout</a>
            </div>

        </div>


    </footer>

    <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">New message</h5>
            <button type="button" class="close" @click="dismissCheckout" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form>
            <div class="form-group d-flex">
                <label for="recipient-name" class="col-form-label">Total Paid Amount:</label>
                <input type="text" v-model="totalPaid" class="form-control m-auto w-auto" id="totalPaidAmount" @keyup="calculateChange">
            </div>
            <div class="form-group d-flex">
                <label for="message-text" class="col-form-label">Total:</label>
                <span class="m-auto">{{payableAmount}}</span>
            </div>
            <div class="form-group d-flex">
                <label for="message-text" class="col-form-label">Payment Method:</label>
                <select v-model="form.payment_method_id"
                    @change="
                        paymentMethodOnChanges($event)
                    "
                    name="status_type"
                    id="payment_method"
                    class="form-control m-auto"
                    style=""
                >
                    <option value="1">Cash</option>
                    <option value="2">Debit Card</option>
                </select>
            </div>
            <div class="form-group d-flex">
                <label for="message-text" class="col-form-label">Change:</label>
                <span class="m-auto">{{changes}}</span>
            </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="dismissCheckout">Close</button>
            <button type="button" class="btn btn-primary" @click="payment">Submit</button>
        </div>
        </div>
    </div>
    </div>

    </div>


</template>

<script>
export default {
    mounted() {
            console.log("Component mounted.");
            this.getProducts();
        },
    data() {
        return {
            check: false,
            products: {},
            productData: {},
            productQty: {},
            form: new Form({
                product_id: null,
                quantity: 0,
                payment_method_id: 1,
            }),
            payableAmount: 0,
            paymentSuccess: 0,
            changes : 0,
            totalPaid: 0,
        };
    },
    methods: {
        loadCart(product) {
            let html = "";
            let section = "";
            let $this = this;
            let cartSectionId = "cart_" + product.product_id;
            this.productQty[product.id] = product.quantity;
            if (product.quantity <= 0) {
                $("#" + cartSectionId).html(html);
                return true;
            }

            html += '<article class="product">';
            html += "<header>";
            html += '<a class="removeoff">';
            html += "<img ";
            html += 'src="http://www.astudio.si/preview/blockedwp/wp-content/uploads/2012/08/1.jpg"';
            html += 'alt=""';
            html += "/>";
            html += "</a>";
            html += "</header>";
            html += '<div class="content">';
            html += "    <h1>" + this.productData[product.product_id].name + "</h1>";
            // html +=      product.description;
            html += "    perspiciatis ea ad omnis provident laborum dolore in atque.";
            html += "</div>";
            html += '<footer class="content" product_id=' + product.product_id + ">";
            html += '<span class="qt-minus">-</span>';
            html += '<span class="qt">' + product.quantity + "</span>";
            html += '<span class="qt-plus">+</span>';
            html += '<h2 class="full-price">' + product.amount + "</h2>";
            html += '<h2 class="price">' + this.productData[product.product_id].price + "</h2>";
            html += "</footer>";
            html += "</article>";

            let exists = $("#user-cart").has("#" + cartSectionId).length;
            if (exists > 0) {
                $("#" + cartSectionId).html(html);
            } else {
                section += '<section id="' + cartSectionId + '">';
                section += html;
                section += "</section>";

                $("#user-cart").append(section);
            }

            $(".subtotal span").html(product.calculated_amount);
            $(".tax span").html(product.tax+' %');
            $(".service_charge span").html(product.service_charge+' %');
            $(".total span").html(product.payable_amount);
            this.payableAmount = product.payable_amount;

            $(".qt-plus").click(function () {
                let productId = $(this).parent().attr("product_id");
                $this.increment(productId);
            });

            $(".qt-minus").click(function () {
                let productId = $(this).parent().attr("product_id");
                $this.decrement(productId);
            });
        },
        increment(productId) {
            if (this.paymentSuccess) {
                this.form.reset();
            }

            let quantity = this.productQty[productId] ? this.productQty[productId] : 0;
            this.productQty[productId] = quantity + 1;

            this.form.product_id = productId;
            this.form.quantity = this.productQty[productId];
            this.addCart();
        },
        decrement(productId) {
            let quantity = this.productQty[productId] ? this.productQty[productId] : 0;

            if (quantity <= 0) {
            return false;
            }

            this.productQty[productId] = quantity - 1;
            this.form.product_id = productId;
            this.form.quantity = this.productQty[productId];
            this.addCart();
        },
        addCart() {
            this.form
            .put("api/cart")
            .then(({ data }) => {
                this.loadCart(data.data);
            })
            .catch((error) => {
                console.log(error.response.data);
            });
        },
        loadProducts(products) {
            let html = "";
            let $this = this;

            html += '<article id="product-list" class="product">';
            products.data.forEach((product, index) => {
            this.productData[product.id] = product;
            html += '<header class="productlist" product_id=' + product.id + ">";
            html += '   <a class="productid">' + product.id + "</a>";
            html += '   <a class="productname">' + product.name + "</a>";
            html += "</header>";
            });
            html += "</article>";

            // document.getElementById('product').innerHTML  = html;
            $("#product-cart").html(html);

            $(".productlist").on("click", function (e) {
                let productId = $(e.currentTarget).attr("product_id");
                $this.increment(productId);
            });
        },
        getCarts() {
            axios.get("api/carts").then(({ data }) => {
                this.products = data.data;
                this.loadProducts(this.products);
            });
        },
        getProducts() {
            axios.get("api/products").then(({ data }) => {
                this.products = data.data;
                this.loadProducts(this.products);
            });
        },
        checkout() {
            if (!Object.keys(this.productQty).length) {
                alert('please add some product.');
                return false;
            }

            if (this.form.order_id) {
                $('#checkoutModal').modal('show');
            } else {
                this.form
                .post("api/cart/checkout")
                .then(({ data }) => {
                    this.form.order_id = data.data.id;

                    $('#checkoutModal').modal('show');
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
            }

        },
        cancel() {
            if (!this.form.order_id) {
                return false;
            }

            this.form
            .post("api/order/request-cancel")
            .then(({ data }) => {
                $("#user-cart").html("");
                alert(data.message);
            })
            .catch((error) => {
                console.log(error.response.data);
            });
        },
        payment() {
            if(this.totalPaid <= 0) {
                alert ('please key in amount to pay');
                return false;
            }

            if(this.totalPaid < this.payableAmount) {
                alert ('total paid amount cant be less than payable amount.');
                return false;
            }

            this.form
            .post("api/order/payment")
            .then(({ data }) => {
                console.log("get submit payment :");
                console.log(data.data);
                this.form.order_id = data.data.id;
                this.paymentSuccess = 1;
                this.productQty = {};
                $(".subtotal span").html(0);
                $(".tax span").html('-');
                $(".service_charge span").html('-');
                $(".total span").html(0);
                $("#user-cart").html("");
                $('#checkoutModal').modal('hide');
                alert(data.message);
            })
            .catch((error) => {
                let response = error.response.data;
                alert(response.message);

            });
        },
        paymentMethodOnChanges(event) {
            this.form.payment_method_id = event.target.value;
        },
        calculateChange() {
            let amount = this.totalPaid - this.payableAmount;

            if (amount <= 0) {
                amount = 0;
            }

            this.changes = amount;
        },
        dismissCheckout() {
            $('#checkoutModal').modal('hide');
        }
    },
};
</script>

<style src="@css/cart.css"></style>
