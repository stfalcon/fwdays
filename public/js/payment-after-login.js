var e_slug = Cookies.get('event');
if (e_slug) {
    Cookies.remove('event', { path: '/', http: false, secure : false });
    Cookies.remove('bye-event', { path: '/', http: false, secure : false });
    window.location.pathname = homePath+"event/"+e_slug+"/pay";
}
