document.addEventListener('alpine:init', () => {
    Alpine.data('chats', ($wire) => ({
        loading: {
            type: null,
            identifier: null,
        },
        threadId: null,
        startFolder: null,
        dragging: false,
        expandedFolder: null,
        async drop(folderId) {
            try {
                if (this.startFolder === folderId) {
                    return;
                }

                const result = await this.$wire.movedThread(this.threadId, folderId);

                if (result.original.success) {
                    if (folderId) {
                        this.expandedFolder = folderId;
                    }

                    new FilamentNotification()
                        .icon('heroicon-o-check-circle')
                        .title(result.original.message)
                        .iconColor('success')
                        .send();
                } else {
                    new FilamentNotification()
                        .icon('heroicon-o-x-circle')
                        .title(result.original.message)
                        .iconColor('danger')
                        .send();
                }
            } catch (exception) {
                new FilamentNotification()
                    .icon('heroicon-o-x-circle')
                    .title('Something went wrong, please try again later.')
                    .iconColor('danger')
                    .send();
            } finally {
                this.threadId = null;
                this.startFolder = null;
            }
        },
        start(threadId, folderId) {
            this.dragging = true;
            this.threadId = threadId;
            this.startFolder = folderId;
        },
        end() {
            this.dragging = false;
        },
        expand(folderId) {
            if (this.expandedFolder === folderId) {
                this.expandedFolder = null;
            } else {
                this.expandedFolder = folderId;
            }
        },
        expanded(folderId) {
            return this.expandedFolder === folderId;
        },
        async selectThread(thread) {
            this.loading.type = 'thread';
            this.loading.identifier = thread.id;

            await $wire.selectThread(thread);

            this.loading.type = null;
            this.loading.identifier = null;

            let url = new URL(window.location);
            history.pushState({}, '', url.pathname);
        },
        async moveThread(threadId) {
            this.loading.type = 'moveThreadAction';
            this.loading.identifier = threadId;

            await $wire.mountAction('moveThread', { thread: threadId });

            this.loading.type = null;
            this.loading.identifier = null;
        },
        async editThread(threadId) {
            this.loading.type = 'editThreadAction';
            this.loading.identifier = threadId;

            await $wire.mountAction('editThread', { thread: threadId });

            this.loading.type = null;
            this.loading.identifier = null;
        },
        async deleteThread(threadId) {
            this.loading.type = 'deleteThreadAction';
            this.loading.identifier = threadId;

            await $wire.mountAction('deleteThread', { thread: threadId });

            this.loading.type = null;
            this.loading.identifier = null;
        },
        async renameFolder(folderId) {
            this.loading.type = 'renameFolderAction';
            this.loading.identifier = folderId;

            await $wire.mountAction('renameFolder', { folder: folderId });

            this.loading.type = null;
            this.loading.identifier = null;
        },
        async deleteFolder(folderId) {
            this.loading.type = 'deleteFolderAction';
            this.loading.identifier = folderId;

            await $wire.mountAction('deleteFolder', { folder: folderId });

            this.loading.type = null;
            this.loading.identifier = null;
        },
    }));
});

document.addEventListener('livewire:init', () => {
    Livewire.on('remove-thread-param', (event) => {
        let url = new URL(window.location);
        history.pushState({}, '', url.pathname);
    });
});
