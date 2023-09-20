<!-- Modal -->
<form action="/task/copy" method="post" id="copy-task-form">
    @csrf
    <div class="modal fade" id="task-copy" tabindex="-1" role="dialog" aria-labelledby="task-copy-label"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="task-copyLabel">タスクの複製</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- <p class="copy-message">最新のスプリントに複製しますか。</p> --}}
                    <p class="copy-message">最新のスプリントに複製しますか。</p>
                    <div class="form-group">
                        <div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">複製</button>
                </div>
            </div>
        </div>
    </div>
</form>
