<?php
/**
 * Footer Section (Light theme)
 */
?>
<!-- Footer Section Start -->
<footer class="footer-section mt-auto">
    <div class="container-fluid">
        <div class="footer-copyright text-center">
            <p class="text-body-light">
                <?php echo date('Y'); ?> &copy; <?php echo SITE_NAME; ?>. Dikuasakan oleh Universiti Pertahanan Nasional Malaysia (UPNM)
            </p>
        </div>
    </div>
</footer>
<!-- Footer Section End -->
<!-- Footer Section End -->

<!-- Small script: if page content is shorter than viewport, pin footer to bottom -->
<script>
    (function(){
        var footer = document.querySelector('.footer-section');
        var contentBody = document.querySelector('.content-body');
        // use the full content-body as the layout container so footer spans the full content area
        var layoutContainer = contentBody || document.querySelector('.main-wrapper');
        var innerContainer = contentBody ? contentBody.querySelector('.container-fluid') : null;

        function applyFixedLayout(pin){
            try{
                if (!footer || !container || !contentBody) return;
                if (pin) {
                    document.body.classList.add('sticky-footer-fixed');
                    // compute layout container rect relative to viewport so footer spans full content area
                    var rect = layoutContainer.getBoundingClientRect();
                    footer.style.boxSizing = 'border-box';
                    // set left and width so footer covers the same horizontal space as content-body
                    footer.style.left = Math.max(0, rect.left) + 'px';
                    footer.style.width = rect.width + 'px';
                    // clear right to avoid conflicts
                    footer.style.right = '';
                    // make inner container full width and remove gutters
                    try{
                        var fc = footer.querySelector('.container-fluid');
                        if (fc) {
                            fc.style.maxWidth = 'none';
                            fc.style.width = '100%';
                            fc.style.paddingLeft = '0';
                            fc.style.paddingRight = '0';
                        }
                    }catch(e){}
                    // ensure content has space for fixed footer
                    contentBody.style.paddingBottom = footer.offsetHeight + 'px';
                } else {
                    document.body.classList.remove('sticky-footer-fixed');
                    footer.style.left = '';
                    footer.style.width = '';
                    footer.style.boxSizing = '';
                    footer.style.right = '';
                    try{ var fc = footer.querySelector('.container-fluid'); if(fc) { fc.style.maxWidth=''; fc.style.width = ''; fc.style.paddingLeft=''; fc.style.paddingRight=''; } }catch(e){}
                    contentBody.style.paddingBottom = '';
                }
            } catch(e) { console && console.warn && console.warn(e); }
        }

        function updateStickyFooter(){
            try{
                if (!footer || !container) return;
                var docHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
                var viewH = window.innerHeight || document.documentElement.clientHeight;
                var pin = docHeight <= viewH + 2;
                applyFixedLayout(pin);
            } catch(e) { console && console.warn && console.warn(e); }
        }

        // Recompute when sidebar visibility changes. Observe side-header class mutations.
        function watchSidebar(){
            try{
                var side = document.querySelector('.side-header');
                if (!side) return;
                var mo = new MutationObserver(function(){ setTimeout(updateStickyFooter, 80); });
                mo.observe(side, { attributes: true, attributeFilter: ['class'] });
            }catch(e){ }
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ container = contentBody ? contentBody.querySelector('.container-fluid') : document.querySelector('.container-fluid'); watchSidebar(); updateStickyFooter(); });
        else { container = contentBody ? contentBody.querySelector('.container-fluid') : document.querySelector('.container-fluid'); watchSidebar(); updateStickyFooter(); }
        window.addEventListener('resize', updateStickyFooter);
        // also update after a short delay for pages that load content async
        setTimeout(updateStickyFooter, 600);
    })();
</script>

<!-- JS -->
<script src="<?php echo asset('light/js/vendor/modernizr-3.6.0.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/jquery-3.3.1.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/popper.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/bootstrap.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/plugins/perfect-scrollbar.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/plugins/tippy4.min.js.js'); ?>"></script>
<script src="<?php echo asset('light/js/main.js'); ?>"></script>
</body>
</html>

