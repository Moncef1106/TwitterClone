document.addEventListener('DOMContentLoaded', () => {
    // Like functionality
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', () => {
            const postId = button.getAttribute('data-post-id');
            fetch('like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'liked') {
                            button.textContent = 'Liked ';
                            button.classList.add('liked');
                        } else if (data.action === 'unliked') {
                            button.textContent = 'Unliked';
                            button.classList.remove('liked');
                        }
                        // Update like count if provided
                        if (data.likes_count !== undefined) {
                            const likeCount = button.closest('.post').querySelector('.like-count');
                            if (likeCount) {
                                likeCount.textContent = data.likes_count;
                            }
                        }
                    } else {
                        alert(data.message || 'Failed to like/unlike');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to process like action');
                });
        });
    });

    // Comment functionality
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const postId = form.getAttribute('data-post-id');
            const commentInput = form.querySelector('input[name="comment"]');
            const comment = commentInput.value.trim();

            if (!comment) {
                alert('Please enter a comment');
                return;
            }

            fetch('comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, comment })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        form.reset(); // Clear the comment input
                        fetchComments(postId); // Refresh comments immediately
                    } else {
                        alert(data.message || 'Failed to add comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to post comment');
                });
        });
    });

    // Function to fetch and display comments
    function fetchComments(postId) {
        fetch(`get_comments.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to fetch comments');
                }
                const commentList = document.querySelector(`.comments[data-post-id="${postId}"] ul`);
                if (!commentList) return;

                commentList.innerHTML = ''; // Clear existing comments
                data.comments.forEach(comment => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <strong>${escapeHtml(comment.username)}:</strong> 
                        ${escapeHtml(comment.comment)}
                        <small>(${new Date(comment.created_at).toLocaleString()})</small>
                    `;
                    commentList.appendChild(li);
                });
            })
            .catch(error => {
                console.error('Error fetching comments:', error);
            });
    }

    // Helper function to escape HTML and prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Fetch comments for all posts on page load
    document.querySelectorAll('.post').forEach(post => {
        const postId = post.getAttribute('data-post-id');
        fetchComments(postId);
    });
});