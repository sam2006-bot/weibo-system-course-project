// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. 发布微博 (支持多图上传)
    const publishBtn = document.getElementById('publish-btn');
    if (publishBtn) {
        publishBtn.addEventListener('click', function() {
            const content = document.getElementById('weibo-content').value;
            const imageInput = document.getElementById('weibo-image');
            // 获取所有选中的文件
            const files = imageInput && imageInput.files ? imageInput.files : [];
            
            if (!content.trim() && files.length === 0) {
                alert('内容或图片不能为空');
                return;
            }
            
            if (files.length > 9) {
                alert('最多只能选择 9 张图片');
                return;
            }

            const formData = new FormData();
            formData.append('content', content);
            
            // 循环添加到 formData，注意 key 是 images[]
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            // 添加 loading 状态
            publishBtn.disabled = true;
            publishBtn.textContent = '发布中...';

            fetch('api/post_weibo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('发布成功！');
                    if (imageInput) imageInput.value = '';
                    location.reload(); // 刷新页面显示新内容
                } else {
                    alert(data.message || '发布失败');
                    publishBtn.disabled = false;
                    publishBtn.textContent = '发布';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('网络错误');
                publishBtn.disabled = false;
                publishBtn.textContent = '发布';
            });
        });
    }

    // 2. 点赞功能 (Ajax + JS动态效果)
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.id;
            const likeCountSpan = this.querySelector('.like-count');

            fetch('api/like_weibo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 动态更新数字
                    likeCountSpan.textContent = data.new_count;
                    // 动态切换样式
                    if (data.action === 'liked') {
                        this.classList.add('active');
                        this.style.transform = "scale(1.2)"; // 简单的放大动画
                        setTimeout(() => this.style.transform = "scale(1)", 200);
                    } else {
                        this.classList.remove('active');
                    }
                } else {
                    if (data.message === '请先登录') {
                        window.location.href = 'login.php';
                    } else {
                        alert(data.message);
                    }
                }
            });
        });
    });

    // 3. 关注功能
    document.querySelectorAll('[data-follow-btn]').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            if (!userId) return;

            const wasFollowing = this.classList.contains('is-following');
            const setFollowState = (isFollowing) => {
                this.classList.toggle('is-following', isFollowing);
                this.setAttribute('aria-pressed', isFollowing ? 'true' : 'false');
                this.textContent = isFollowing ? '已关注' : '关注';
            };

            this.disabled = true;
            this.textContent = wasFollowing ? '取消中...' : '关注中...';

            fetch('api/follow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${encodeURIComponent(userId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setFollowState(!!data.is_following);
                } else {
                    if (data.message === '请先登录') {
                        window.location.href = 'login.php';
                        return;
                    }
                    alert(data.message || '操作失败');
                    setFollowState(wasFollowing);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('网络错误');
                setFollowState(wasFollowing);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // 4. 评论展开/收起
    document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.id;
            const commentSection = document.getElementById(`comments-${postId}`);
            
            if (commentSection.style.display === 'block') {
                commentSection.style.display = 'none';
            } else {
                commentSection.style.display = 'block';
            }
        });
    });

    // 5. 发表评论 (Ajax)
    document.querySelectorAll('.submit-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.id;
            const input = this.previousElementSibling;
            const content = input.value;

            if(!content.trim()) return;

            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('content', content);

            fetch('api/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 动态添加评论到列表，无需刷新
                    const list = document.getElementById(`comment-list-${postId}`);
                    const newComment = document.createElement('div');
                    newComment.style = "border-top: 1px dashed #eee; padding: 5px 0; font-size: 13px;";
                    newComment.innerHTML = `<span style="color:#fa7d3c">${data.username}:</span> ${data.content}`;
                    list.insertBefore(newComment, list.firstChild);
                    input.value = ''; // 清空输入框
                } else {
                    alert(data.message);
                }
            });
        });
    });

    // 6. 侧边栏用户菜单 (首页)
    const userMenu = document.querySelector('[data-user-menu]');
    if (userMenu) {
        const trigger = userMenu.querySelector('[data-user-menu-trigger]');
        const panel = userMenu.querySelector('[data-user-menu-panel]');

        if (trigger && panel) {
            const setMenuOpen = (open) => {
                userMenu.classList.toggle('is-open', open);
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.hidden = !open;
                if (!open && userMenu.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
            };

            setMenuOpen(false);

            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = !userMenu.classList.contains('is-open');
                setMenuOpen(isOpen);
            });

            panel.addEventListener('click', function(event) {
                const menuItem = event.target.closest('a');
                if (menuItem) {
                    setMenuOpen(false);
                }
            });

            document.addEventListener('click', function(event) {
                if (!userMenu.contains(event.target)) {
                    setMenuOpen(false);
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    setMenuOpen(false);
                }
            });
        }
    }

    // 7. 头像上传 (个人资料视图)
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('avatar', file);

            const img = document.getElementById('profile-avatar-img');
            if (!img) return;

            const oldSrc = img.src;
            img.style.opacity = '0.5';

            fetch('api/upload_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    img.style.opacity = '1';
                    if (data.success) {
                        img.src = data.avatar_url;
                        alert('头像更换成功！');
                    } else {
                        alert(data.message || '上传失败');
                        img.src = oldSrc;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('网络错误');
                    img.style.opacity = '1';
                });
        });
    }
});
