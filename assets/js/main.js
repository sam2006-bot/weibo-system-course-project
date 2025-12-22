// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. 发布微博 (Ajax)
    const publishBtn = document.getElementById('publish-btn');
    if (publishBtn) {
        publishBtn.addEventListener('click', function() {
            const content = document.getElementById('weibo-content').value;
            const imageInput = document.getElementById('weibo-image');
            const imageFile = imageInput && imageInput.files ? imageInput.files[0] : null;
            const maxSize = 5 * 1024 * 1024;
            if (!content.trim() && !imageFile) {
                alert('内容或图片不能为空');
                return;
            }
            if (imageFile && imageFile.size > maxSize) {
                alert('图片大小不能超过 5MB');
                return;
            }

            const formData = new FormData();
            formData.append('content', content);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            fetch('api/post_weibo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('发布成功！');
                    if (imageInput) {
                        imageInput.value = '';
                    }
                    location.reload(); // 简单处理：刷新页面显示新内容
                } else {
                    alert(data.message || '发布失败');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // 2. 点赞功能 (Ajax + JS动态效果)
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.id;
            const likeCountSpan = this.querySelector('.like-count');
            const icon = this.querySelector('i'); // 假设用了 FontAwesome，或者是文字

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
                        // JS 动态效果 2：弹出登录模态框（假设有实现，或者跳转）
                        window.location.href = 'login.php';
                    } else {
                        alert(data.message);
                    }
                }
            });
        });
    });

    // 3. 评论展开/收起 (JS动态效果 3: Slide Toggle 模拟)
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

    // 4. 发表评论 (Ajax)
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
});
