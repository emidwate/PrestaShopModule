<div class="coupon-container" style="background-color: {$couponBgColor|default: "white"};">
<div class="coupon-image-container">
    <img src="{$moduleDir}views/img/gift.jpg" alt="Coupon gift image." class="coupon-main-image">
    <h3 class="coupon-exit-btn">X</h3>
</div>
<div class="coupon-content-container">
    <div class="coupon-top-section">
        <p class="coupon-text">
        {l 
            s='Get %discountAmount% OFF from your next purchase!' 
            sprintf=['%discountAmount%' => {$discountAmount}] 
            d='Modules.Coupon.Coupon'
        }
        </p>
        <p class="coupon-text">{$customSellerMessage}</p>
     </div>
    <div class="coupon-bottom-section">
        <p class="coupon-text">
        {l 
            s='Valid through %discountExpirationDate%' 
            sprintf=['%discountExpirationDate%'=>{$discountExpirationDate}] 
            d='Modules.Coupon.Coupon' 
        }
        </p>
        <button class="coupon-show-code-btn">
        {l 
            s='Show code' 
            d='Modules.Coupon.Coupon'
        }
        </button>
        <p class="coupon-text clipboard-message"></p>
    </div>
</div>
</div>
<script type="text/javascript"> 
    let discountCode = "{$discountCode}"
    let displayDurationUserTime = "{$displayDuration}"
    let intervalDurationUserTime = "{$intervalDuration}"
</script>