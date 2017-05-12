$(document).ready(function() {

  $('.btn').on('click', function(e) {
    e.preventDefault()
    var name = $('#nameForm').val()
    $('#nameForm').val('')
    $.ajax({
      url: 'form.php',
      method: 'POST',
      data: JSON.stringify({ name: name }),
      success: function(response) {
        console.info(response)
        if (response.error) {
          $('#message').text('Server Error!')
        } else if (response.ok) {
          $('#message').text('Name Saved!')
          setTimeout(function() {
            $('#message').text('Simple PHP Form')
          }, 2000)
        }
      },
      error: function(error) {
        console.error(error)
      }
    })
  })

})
