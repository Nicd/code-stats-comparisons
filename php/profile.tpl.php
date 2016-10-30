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
<div class="row">
  <div id="profile-elm-total-container" class="col-xs-12 col-sm-7">
    <h3>
      Level
      <?= get_level($total_xp) ?>
      (<?= format_xp($total_xp) ?>&nbsp;XP)

      <?php if ($total_new_xp > 0): ?>
        <sup>
          (+<?= format_xp($total_new_xp) ?>)
        </sup>
      <?php endif; ?>
    </h3>

    <div class="progress">
      <?php list($old_width, $new_width) = get_xp_bar_widths($total_xp, $total_new_xp); ?>
      <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?= $old_width ?>%">
        <span class="sr-only">Level progress <?= $old_width ?> %.</span>
      </div>
      <div class="progress-bar progress-bar-striped progress-bar-warning" role="progressbar" style="width: <?= $new_width ?>%">
        <span class="sr-only">Recent level progress <?= $new_width ?> %.</span>
      </div>
    </div>
  </div>

  <div class="col-xs-12 col-sm-5">
    <h2
      id="profile-username"
      data-name="<?= $user['username'] ?>"
    >
      <?= $user['username'] ?>
    </h2>
    <ul class="profile-detail-list">
      <li>
        Programming since
        <time datetime="<?= $user['inserted_at'] ?>">
          <?= $user['inserted_at'] ?></time>.
      </li>
      <li>Average <?= format_xp($xp_per_day) ?>&nbsp;XP per day.</li>
      <li>
        Last coded
        <?php if ($last_day_coded !== null): ?>
          on
          <time datetime="<?= $last_day_coded ?>">
            <?= $last_day_coded ?></time>.
        <?php else: ?>
          <em>never</em>.
        <?php endif; ?>
      </li>
    </ul>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <hr />
  </div>
</div>

<?php if (!has_language_xps¿($language_xps)): ?>
  <div class="jumbotron">
    <p class="lead">
      Blah
    </p>
  </div>
<?php else: ?>
  <div id="profile-elm-main-container">
    <div class="row">
      <?php foreach (array_slice($language_xps, 0, LANG_XP_AMNT - 1) as $data): ?>
        <?php list($language, $xp) = $data; ?>
        <?php $new_xp = get($new_xps, $language['id'], 0); ?>

        <div class="col-xs-12 profile-language-progress">
          <h4>
            <?= $language['name'] ?>
            level
            <?= get_level($xp) ?>
            (<?= format_xp($xp) ?>&nbsp;XP)

            <?php if ($new_xp > 0): ?>
              <sup>
                (+<?= format_xp($new_xp) ?>)
              </sup>
            <?php endif; ?>
          </h4>
          <div class="progress">
            <?php list($old_width, $new_width) = get_xp_bar_widths($xp, $new_xp) ?>
            <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?= $old_width ?>%">
              <span class="sr-only">Level progress <?= $old_width ?> %.</span>
            </div>
            <div class="progress-bar progress-bar-striped progress-bar-warning" role="progressbar" style="width: <?= $new_width ?>%">
              <span class="sr-only">Recent level progress <?= $new_width ?> %.</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <hr />

        <p class="text-center">
          <small>
            XP gained in the last 12 hours is highlighted. <noscript>Also, if you turned on JavaScript for this site, you would see these numbers update live.</noscript>
          </small>
        </p>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <hr />
      </div>
    </div>

    <div class="row">
      <?php if (has_more_language_xps¿($language_xps)): ?>
        <div class="col-xs-12 col-sm-6">
          <h4>Other languages</h4>

          <ol start="11">
            <?php foreach (array_slice($language_xps, LANG_XP_AMNT) as $data): ?>
              <?php list($language, $xp) = $data; ?>
              <?php $new_xp = get($new_xps, $language['id'], 0); ?>
              <li class="profile-more-language-progress" >
                <strong><?= $language['name'] ?></strong>
                level
                <?= get_level($xp) ?>
                (<?= format_xp($xp) ?>&nbsp;XP)

                <?php if ($new_xp > 0): ?>
                  <sup>
                    (+<?= format_xp($new_xp) ?>)
                  </sup>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endif; ?>

      <?php if (has_machine_xps¿($machine_xps)): ?>
        <div class="col-xs-12 col-sm-6">
          <h4>Machines</h4>

          <ol>
            <?php foreach ($machine_xps as $data): ?>
              <?php list($machine, $xp) = $data; ?>
              <?php $new_xp = get($new_machine_xps, $machine['id'], 0) ?>

              <li class="profile-machine-progress">
                <strong>
                  <?= $machine['name'] ?>
                </strong>

                level
                <?= get_level($xp) ?>
                (<?= format_xp($xp) ?>&nbsp;XP)

                <?php if ($new_xp > 0): ?>
                  <sup>
                    (+<?= format_xp($new_xp) ?>)
                  </sup>
                <?php endif; ?>

                <div class="progress">
                  <?php list($old_width, $new_width) = get_xp_bar_widths($xp, $new_xp) ?>
                  <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?= $old_width ?>%">
                    <span class="sr-only">Level progress <?= $old_width ?> %.</span>
                  </div>
                  <div class="progress-bar progress-bar-striped progress-bar-warning" role="progressbar" style="width: <?= $new_width ?>%">
                    <span class="sr-only">Recent level progress <?= $new_width ?> %.</span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
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
