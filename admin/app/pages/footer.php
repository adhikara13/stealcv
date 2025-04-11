<!-- Import Js Files -->
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/libs/jquery/jquery-3.7.1.min.js"></script>

<script src="assets/libs/datatables/jquery.dataTables.min.js"></script>

<!-- select2 lib -->
<script src="assets/libs/select2/select2.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop(); 

    const menuItems = document.querySelectorAll('#sidebarnav .sidebar-item');

    menuItems.forEach(item => {
      const link = item.querySelector('a.sidebar-link');
      if (link)
    {
        const linkPage = link.getAttribute('href').split('/').pop();

        if (linkPage === currentPage) {
          item.classList.add('selected'); 
        }
      }
    });
  });
  
</script>

<!-- logs worker -->
<script src="assets/libs/toastr/toastr.min.js"></script>

<script>
var theme = <?php echo $_SESSION['theme']; ?>;

$(document).ready(function()
{
    UpdateThemeButton();
});

function UpdateThemeButton()
{
    if(theme == 1)
    {
        $('html').attr('data-bs-theme', 'dark');
        $('#light_theme_button').css('display', '');
        $('#dark_theme_button').css('display', 'none');
    }
    else
    {
        $('html').attr('data-bs-theme', 'light');
        $('#dark_theme_button').css('display', '');
        $('#light_theme_button').css('display', 'none');
    }
}

function ChangeTheme(_theme)
{
    $.post('api/dashboard', 
    { 
        method: 'change_theme',
        theme: _theme
    },
    function(response) 
    {
        if (response.success)
        {
            theme = _theme;
            UpdateThemeButton();
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

</script>