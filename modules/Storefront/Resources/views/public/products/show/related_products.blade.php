<section class="landscape-products-wrap">
    <div class="landscape-products-inner">
        <div class="products-header">
            <h5 class="section-title">
                {{ trans("storefront::product.related_products") }}
            </h5>
        </div>
    
        <div class="landscape-products swiper" x-ref="landscapeProducts">
            <div class="swiper-wrapper">
                @foreach (range(0, 5) as $skeleton)
                    <div class="swiper-slide swiper-slide-skeleton">
                        @include('storefront::public.partials.product_card_skeleton')
                    </div>
                @endforeach

                @foreach ($relatedProducts as $relatedProduct)
                    <div class="swiper-slide">
                        @include('storefront::public.partials.product_card', [
                            'data' => $relatedProduct
                        ])
                    </div>
                @endforeach
            </div>
    
            <div class="swiper-button-next">
                {{ trans("storefront::layouts.next") }}
            </div>
            
            <div class="swiper-button-prev">
                {{ trans("storefront::layouts.prev") }}
            </div>

            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>