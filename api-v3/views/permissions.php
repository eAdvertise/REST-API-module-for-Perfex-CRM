<?php
defined('BASEPATH') or exit('No direct script access allowed');

$api_permissions       = get_available_api_permissions();
$api_permissions_count = count($api_permissions);

// $user_api is set when editing an existing token; guard so the closure below
// always has it defined.
$user_api = isset($user_api) ? $user_api : null;

/**
 * Render a single feature row (feature label + capability checkboxes).
 * The capability checkbox name/value contract is unchanged so the existing
 * save handler keeps working.
 */
$render_api_permission_row = function ($feature, $permission) use ($user_api) {
    ?>
    <tr data-name="<?php echo $feature; ?>">
        <td>
            <div class="checkbox checkbox-primary no-margin">
                <input type="checkbox" class="api-feature-all" id="all_<?php echo $feature; ?>">
                <label for="all_<?php echo $feature; ?>"><b><?php echo $permission['name']; ?></b></label>
            </div>
        </td>
        <td>
            <?php foreach ($permission['capabilities'] as $capability => $name) {
                $checked = '';
                if ($user_api !== null && api_can($user_api['id'] ?? '', $feature, $capability)) {
                    $checked = ' checked ';
                } ?>
                <div class="checkbox" style="padding-left: 20px">
                    <input type="checkbox"<?php echo $checked; ?> class="capability" id="<?php echo $feature . '_' . $capability; ?>" name="permissions[<?php echo $feature; ?>][]" value="<?php echo $capability; ?>">
                    <label for="<?php echo $feature . '_' . $capability; ?>"> <?php echo $name; ?></label>
                </div>
            <?php } ?>
        </td>
    </tr>
    <?php
};
?>

<div class="row">
   <div class="col-md-12">
      <p class="bold no-margin"><?php echo _l('permissions'); ?></p>
      <div class="api-perms-toolbar clearfix" style="margin: 10px 0;">
         <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-default" id="api-perms-select-all"><i class="fa fa-check-square-o"></i> Select all</button>
            <button type="button" class="btn btn-default" id="api-perms-readonly" title="Grant only read (get / search) capabilities"><i class="fa fa-eye"></i> Read-only</button>
            <button type="button" class="btn btn-default" id="api-perms-clear-all"><i class="fa fa-square-o"></i> Clear all</button>
         </div>
         <span class="text-muted" style="margin-left: 10px; line-height: 30px;">
            <b><span id="api-perms-count">0</span></b> selected
         </span>
         <span class="text-muted pull-right" style="line-height: 30px;">
            Tip: click a feature's checkbox to toggle all of its capabilities at once.
         </span>
      </div>
   </div>
</div>

<div class="row" id="api-permissions">
   <div class="col-md-6">
      <div class="panel_s">
         <div class="panel-body">
            <div class="table-responsive">
               <table class="table table-bordered roles no-margin">
                  <thead>
                     <tr>
                        <th>Feature</th>
                        <th>Capabilities</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                        $api_permission_index = 0;
                        foreach ($api_permissions as $feature => $permission) {
                           $api_permission_index += 1;
                           if ($api_permission_index >= floor($api_permissions_count / 2)) continue;
                           $render_api_permission_row($feature, $permission);
                        }
                     ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-6">
      <div class="panel_s">
         <div class="panel-body">
            <div class="table-responsive">
               <table class="table table-bordered roles no-margin">
                  <thead>
                     <tr>
                        <th>Feature</th>
                        <th>Capabilities</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                        $api_permission_index = 0;
                        foreach ($api_permissions as $feature => $permission) {
                           $api_permission_index += 1;
                           if ($api_permission_index < floor($api_permissions_count / 2)) continue;
                           $render_api_permission_row($feature, $permission);
                        }
                     ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
// Vanilla JS (no jQuery dependency): Perfex loads jQuery in the page footer, so
// an inline jQuery call here would run before jQuery exists ("jQuery is not
// defined"). This is self-contained and deferred to DOM-ready.
(function () {
   function initApiPerms() {
      var root = document.getElementById('api-permissions');
      if (!root) { return; }

      function caps() {
         return Array.prototype.slice.call(root.querySelectorAll('input.capability'));
      }

      function refresh() {
         var checked = 0;
         caps().forEach(function (c) { if (c.checked) { checked++; } });
         var countEl = document.getElementById('api-perms-count');
         if (countEl) { countEl.textContent = checked; }

         Array.prototype.forEach.call(root.querySelectorAll('tr[data-name]'), function (tr) {
            var rc  = Array.prototype.slice.call(tr.querySelectorAll('input.capability'));
            var all = tr.querySelector('input.api-feature-all');
            if (all) {
               all.checked = rc.length > 0 && rc.every(function (c) { return c.checked; });
            }
         });
      }

      function setAll(pred) {
         caps().forEach(function (c) { c.checked = pred(c); });
         refresh();
      }

      function bindClick(id, handler) {
         var el = document.getElementById(id);
         if (el) { el.addEventListener('click', handler); }
      }

      bindClick('api-perms-select-all', function (e) { e.preventDefault(); setAll(function () { return true; }); });
      bindClick('api-perms-clear-all',  function (e) { e.preventDefault(); setAll(function () { return false; }); });
      // Read-only: only the "read" capabilities (get / search_get / get_value).
      bindClick('api-perms-readonly',   function (e) {
         e.preventDefault();
         setAll(function (c) {
            var v = c.value;
            return v === 'get' || v === 'search_get' || v === 'get_value';
         });
      });

      root.addEventListener('change', function (e) {
         var t = e.target;
         if (!t || !t.classList) { return; }
         if (t.classList.contains('api-feature-all')) {
            var tr = t.closest ? t.closest('tr') : null;
            if (tr) {
               Array.prototype.slice.call(tr.querySelectorAll('input.capability')).forEach(function (c) {
                  c.checked = t.checked;
               });
            }
            refresh();
         } else if (t.classList.contains('capability')) {
            refresh();
         }
      });

      refresh();
   }

   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initApiPerms);
   } else {
      initApiPerms();
   }
})();
</script>
