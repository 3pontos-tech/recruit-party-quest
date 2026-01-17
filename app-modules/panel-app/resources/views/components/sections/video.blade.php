<script>
    function scrollZoomLogic() {
        return {
            scale: 1,
            visible: false,
            minScale: 1,
            maxScale: 3.8,
            animationCutoff: 0.7,
            hasStarted: false,

            init() {
                this.setMaxScale();
            },

            setMaxScale() {
                if (window.innerWidth < 821) {
                    this.maxScale = 12;
                } else if (window.innerWidth < 1024) {
                    this.maxScale = 8;
                }
            },

            calculateProgress() {
                const container = this.$el;
                const rect = container.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const scrollableDistance = container.offsetHeight - viewportHeight;
                let scrollPosition = -rect.top;

                let rawProgress = Math.max(0, Math.min(1, scrollPosition / scrollableDistance));

                if (rawProgress >= this.animationCutoff) {
                    this.visible = true;
                } else {
                    let zoomProgress = rawProgress / this.animationCutoff;
                    this.scale = this.minScale + (this.maxScale - this.minScale) * zoomProgress;
                    this.visible = false;
                }

                const isInsideViewport = rect.bottom > 0 && rect.top < 0;

                if (this.$refs.videoPlayer) {
                    if (this.visible && isInsideViewport) {
                        if (!this.hasStarted) {
                            this.$refs.videoPlayer.play();
                            this.hasStarted = true;
                        }
                    } else {
                        this.$refs.videoPlayer.pause();
                    }
                }
            },
        };
    }
</script>

<section id="video" class="relative h-[300vh]" x-data="scrollZoomLogic()" @scroll.window.passive="calculateProgress">
    <div class="sticky top-8 flex h-screen w-full items-center justify-center overflow-hidden">
        <div class="hp-container relative z-10 flex items-center justify-center">
            <img
                src="{{ asset('images/3pontos/logo-chain-white.png') }}"
                alt="logo chain"
                class="pointer-events-none relative z-20 origin-center will-change-transform"
                :style="`transform: scale(${scale})`"
            />

            <div class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                <x-he4rt::animate-block
                    type="blur"
                    duration="700"
                    class="pointer-events-auto h-[110%] w-[90%] overflow-hidden rounded-lg bg-transparent shadow-xl sm:h-[90%] sm:w-[60%] lg:h-[36.5%] lg:w-[28%]"
                >
                    <video x-ref="videoPlayer" class="h-full w-full object-cover" autoplay muted controls>
                        <source src="{{ asset('videos/3pontos-video.mp4') }}" type="video/mp4" />
                        <source src="{{ asset('videos/3pontos-video.webm') }}" type="video/webm" />
                        Your browser does not support the video tag.
                    </video>
                </x-he4rt::animate-block>
            </div>
        </div>
    </div>
</section>
