

/**
 * 菜单响应
 * @return   {[type]}         [description]
 */
// var pjax_mode = typeof pjax_mode === undefined ? 0 : pjax_mode;
// pjax_mode == 1 && $(".sidebar-menu > li > a").click(function(){
//     // console.log('click');
//     // $(this).addClass("active");
//     $(".sidebar-menu > li").removeClass("active");
//     $(this).parent().addClass("active")
// });

/**
 * 绑定
 */
// $("[type=submit]").click(function(){
// 	console.log('submit');
//     var index = layer.msg('正在处理中……', {
//         icon:16,
//         shade:[0.8, '#393D49'],
//         time:0
//     });
// });

/**
 * 绑定 清理缓存
 */
$("#clear_cache").click(function(){
  console.log('清理缓存')
    layer.confirm('你确定要清除系统缓存吗？', {
      btn: ['确定','点错了'] //按钮
    }, function(){
      var index = layer.msg('正在清理中……', {
          icon:16,
          shade:[0.8, '#393D49'],
          time:0
      });
      $.get(clear_cache_url,function(result){
          layer.close(index);
          if(result.code == 1){
              toastr.success('清理成功');
          }else{
              toastr.error(result.msg,'清理失败');
          }
      });
    });
});