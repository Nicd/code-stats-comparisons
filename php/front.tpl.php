<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Code::Stats is a free stats tracking service for programmers">
    <meta name="author" content="Mikko Ahlroth">

    <title>Code::Stats</title>
    <link rel="stylesheet" href="/app.css">

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <?php if (in_array('user', $request_data)): ?>
      <meta name="session" content="Logged in">
    <?php endif; ?>
  </head>

  <body>
    <div class="container">
      <header class="header">
        <div class="row">
          <div class="col-xs-2">
            <a class="logo" href="/"><img src="/Logo_crushed.png" alt="Code::Stats" title="Code::Stats" height="100%" /></a>
          </div>

          <div class="col-xs-10">
            <nav role="navigation">
              <ul class="nav nav-pills pull-right">
                <?php if (in_array('user', $request_data)): ?>
                  <li><a href="/my/profile">Profile</a></li>
                  <li><a href="/my/preferences">Preferences</a></li>
                  <li><a href="/my/machines">Machines</a></li>
                  <li><a href="/logout">Log out</a></li>
                <?php else: ?>
                  <li><a href="/login">Log in</a></li>
                  <li><a href="/signup">Sign up</a></li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        </div>
      </header>

      <p class="alert alert-info" role="alert"></p>
      <p class="alert alert-success" role="alert"></p>
      <p class="alert alert-danger" role="alert"></p>

      <main role="main">
        <div class="jumbotron">
  <h2>Welcome to Code::Stats!</h2>
  <p class="lead">Write code, level up, show off! A free stats tracking service for programmers.</p>
</div>

<div class="row marketing">
  <div class="col-xs-12 col-lg-4">
    <h4>Write code</h4>
    <p>
      Code::Stats currently has <a href="/plugins">plugins for the Atom editor and the IntelliJ/JetBrains range of IDEs</a>. If you wish to make one for your own favourite editor, you are free to check out the <a href="/api">API documentation</a>!
    </p>
  </div>

  <div class="col-xs-12 col-lg-4">
    <h4>Level up</h4>
    <p>
    You will be awarded with experience points for the amount of programming you do. Watch as your levels grow for each language you use. Identify your strong skill sets and use the data to see where you still have room for improvement.
    </p>
  </div>

  <div class="col-xs-12 col-lg-4">
    <h4>Show off</h4>
    <p>
      Show your personal statistics page to your friends and compare your progress with others. Maybe even have a competition! Or, if you wish, you can keep all your information private and enjoy it in secret.
    </p>

    <p>
      <a href="/users/Nicd">See an example profile →</a>
    </p>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <hr />
  </div>
</div>

<div class="row" id="index-elm-container">
  <div class="col-xs-12">
    <div class="row">
      <div class="col-xs-12 col-md-6">
        <h3>
          Total XP
        </h3>

        <h2>
          <?= format_xp($total_xp) ?>
        </h2>
      </div>

      <div class="col-xs-12 col-md-6">
        <h3>
          Most popular languages
        </h3>

        <ol>
          <?php foreach($most_popular as $tuple): ?>
            <li>
              <?= $tuple[0] ?>: <?= format_xp($tuple[1]) ?>&nbsp;XP
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>


    <div class="row">
      <div class="col-xs-12 text-center">
        <h3>Loading live statistics…</h3>

        <noscript>
          <p class="alert alert-info">
            If you had JavaScript enabled, you would see fancy live updating statistics.
          </p>
        </noscript>
      </div>
    </div>
  </div>
</div>
      </main>

      <footer>
        <hr />

        <p class="text-center">
          <a href="/changes">0.0.1</a>
          —
          <?php list($time, $unit) = calculate_request_time($request_data['request_start_time']); ?>
          <?= $time ?>&nbsp;<?= $unit ?>
          —
          <a href="/api">API docs</a>
          —
          <a href="/terms">Legal</a>
          —
          <a href="/plugins">Plugins</a>
          —
          <a href="/irc">IRC</a>
        </p>

        <p class="text-center">
          Made with <a href="http://elixir-lang.org/">Elixir</a>,
          <a href="http://www.phoenixframework.org/">Phoenix</a>, and
          <a href="http://elm-lang.org/">Elm</a>.
        </p>

        <p class="text-center">
          <small>
            Code::Stats development sponsored by <a href="https://www.vincit.fi/en/">Vincit Oy</a>, the passionate software company.
          </small>
        </p>
      </footer>

    </div> <!-- /container -->

    <!--<script src="/app.js"></script>-->
  </body>
</html>
