<?php

/*
 * UPLOAD STEP
 */
$app->get('/chart/:id/upload', function ($id) use ($app) {
    disable_cache($app);

    check_chart_writable($id, function($user, $chart) use ($app) {
        $datasets = DatawrapperHooks::execute(DatawrapperHooks::GET_DEMO_DATASETS);
        $groups = array();
        if (is_array($datasets)) {
            foreach ($datasets as $ds) {
                if (!isset($groups[$ds['type']])) $groups[$ds['type']] = array('type' => $ds['type'], 'datasets' => array());
                $groups[$ds['type']]['datasets'][] = $ds;
            }
        }

        $page = array(
            'title' => strip_tags($chart->getTitle()).' - '.$chart->getID() . ' - '.__('Upload Data'),
            'chartData' => $chart->loadData(),
            'chart' => $chart,
            'datasets' => $groups,
            'readonly' => !$chart->isDataWritable($user)
        );
        add_header_vars($page, 'chart');
        add_editor_nav($page, 1, $chart);
        $res = $app->response();
        $res['Cache-Control'] = 'max-age=0';

        $page['svelte_data'] = [
            'chart' => $chart,
            'readonly' => !$chart->isDataWritable($user),
            'chartData' => $chart->loadData(),
            'datasets' => $groups,
            'user' => $user->serialize()
        ];

        if ($user->isLoggedIn() && $app->request()->get('beta') !== null) {
            $user->setUserData(['beta_upload' => $app->request()->get('beta') ? '1' : '0']);
            return $app->redirect('/chart/'.$chart->getId().'/upload');
        }

        $app->render('chart/upload.twig', $page);
    });
});
