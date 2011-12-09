var Bus = Backbone.Model.extend({
  initialize: function(spec) {

    if(!spec || !spec.route || !spec.destination || !spec.prediction) {
      throw "InvalidConstructArgs";
    }

    this.set({
      htmlId: 'bus_' + this.cid
    })
  }
});