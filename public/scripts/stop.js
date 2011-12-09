var Stop = Backbone.Model.extend({
  initialize: function(spec) {

    if(!spec || !spec.name) {
      throw "InvalidConstructArgs";
    }

    this.set({
      htmlId: 'stop_' + this.cid
    })
  }
});