/**
 @Name：mapmark V0.2
 @Author：统一 54901801@qq.com
 @Date：2016-11-01
 */
define('pui/mapmark',['jquery','pui/mapmark.css'],function(require,exports,moudles){
	(function($){
		$.fn.mapmark = function(opts){
			opts=$.extend({
				defaultCity : '福州市', // 默认城市
				callback : function(e){
					alert(e.lnglat.lng + "," + e.lnglat.lat + "," + (e.address ? e.address.regeocode.formattedAddress : "未知"));
				}
			},opts);
			this.on('click',function(){
				var mapObj,req = null;
				pui.use('pui/dialog',function(U){
					U({"body":"<div id='mapBoxTemp' style='height:480px;width:600px;overflow:hidden;'>地图加载中...</div><div id='myPageTop'><table><tr><td><label>请输入关键字：</label></td></tr><tr><td><input id='tipinput'/></td></tr></table></div>","yes":function(e){
						req && opts.callback && opts.callback.call(mapObj,req);
					}});
					$('#mapBoxTemp').closest('.ke-dialog-content').parent().css('height','auto');
				});
				window.mapmarkInit = function(){
					mapObj = new AMap.Map("mapBoxTemp",{
						resizeEnable: true
					});
					opts.defaultCity && mapObj.setCity(opts.defaultCity);

					// 添加标注
					var addMarker = function(longitude,latitude,notclear){
						!notclear && mapObj.clearMap(); // 删除标注

						// 添加标注
						var marker = new AMap.Marker({				  
							icon:"http://webapi.amap.com/images/marker_sprite.png",
							position:new AMap.LngLat(longitude,latitude)
						});
						marker.setMap(mapObj);
						marker.setTitle('当前坐标为：'+longitude+','+latitude);
						mapObj.setFitView();
					};

					// 监听点击事件
					AMap.event.addListener(mapObj,'click',function(e){  
						var lnglatXY = [e.lnglat.getLng(),e.lnglat.getLat()];

						addMarker(lnglatXY[0],lnglatXY[1]); // 添加标
						req = e;

						// 查询地址
						var geocoder = new AMap.Geocoder({
							radius: 1000,
							extensions: "all"
						});        
						geocoder.getAddress(lnglatXY, function(status, result) {
							if (status === 'complete' && result.info === 'OK') {
								req.address = result; // result.regeocode.formattedAddress
							}
						}); 
					});

					//输入提示
					var autoOptions = {input: "tipinput"};
					var auto = new AMap.Autocomplete(autoOptions);
					var placeSearch = new AMap.PlaceSearch({
						map: mapObj
					});  //构造地点查询类
						console.log('test11');
					AMap.event.addListener(auto, "select", select);//注册监听，当选中某条记录时会触发
					function select(e) {
						placeSearch.setCity(e.poi.adcode);
						placeSearch.search(e.poi.name);  //关键字查询查询
					}

					mapObj.plugin(["AMap.ToolBar"], function() {
						mapObj.addControl(new AMap.ToolBar());
					});
				};
				$.getScript("http://webapi.amap.com/maps?v=1.3&key=b47dd074683bd20067b9f955b6528c40&plugin=AMap.Geocoder,AMap.Autocomplete,AMap.PlaceSearch&callback=mapmarkInit");
			});
		};
	})(window.jQuery || require('jquery'));
});

