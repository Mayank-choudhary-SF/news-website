<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax_controller extends Home_Core_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Quiz Answers
     */
    public function get_quiz_answers()
    {
        post_method();
        $post_id = $this->input->post('post_id', true);
        $array_quiz_answers = array();
        $questions = $this->quiz_model->get_quiz_questions($post_id);
        if (!empty($questions)) {
            $i = 0;
            foreach ($questions as $question) {
                $correct_answer = $this->quiz_model->get_quiz_question_correct_answer($question->id);
                if (!empty($correct_answer)) {
                    $item = array($question->id, $correct_answer->id);
                    array_push($array_quiz_answers, $item);
                }
                $i++;
            }
        }
        $data = array(
            'result' => 1,
            'array_quiz_answers' => $array_quiz_answers,
        );
        echo json_encode($data);
    }

    /**
     * Get Quiz Results
     */
    public function get_quiz_results()
    {
        post_method();
        $post_id = $this->input->post('post_id', true);
        $array_quiz_results = array();
        $results = $this->quiz_model->get_quiz_results($post_id);
        if (!empty($results)) {
            foreach ($results as $result) {
                $vars = array('result' => $result);
                $html_content = $this->load->view('post/details/_quiz_result', $vars, true);
                //array: [0]: result id, [1]: min correct, [2]: max correct, [3]: html content
                $item = array($result->id, $result->min_correct_count, $result->max_correct_count, $html_content);
                array_push($array_quiz_results, $item);
            }
        }
        $data = array(
            'result' => 1,
            'array_quiz_results' => $array_quiz_results,
        );
        echo json_encode($data);
    }

    /**
     * Show Popular Posts
     */
    public function get_popular_posts()
    {
        $date_type = $this->input->post('date_type', true);
        $lang_id = $this->input->post('lang_id', true);
        $popular_posts = "";
        $html_content = "";
        if ($date_type == "week") {
            $popular_posts = get_cached_data_by_lang('popular_posts_week', $lang_id);
            if (empty($popular_posts)) {
                $popular_posts = $this->post_model->get_popular_posts(7, $lang_id);
                set_cache_data_by_lang('popular_posts_week', $popular_posts, $lang_id);
            }
        } elseif ($date_type == "month") {
            $popular_posts = get_cached_data_by_lang('popular_posts_month', $lang_id);
            if (empty($popular_posts)) {
                $popular_posts = $this->post_model->get_popular_posts(30, $lang_id);
                set_cache_data_by_lang('popular_posts_month', $popular_posts, $lang_id);
            }
        } elseif ($date_type == "year") {
            $popular_posts = get_cached_data_by_lang('popular_posts_year', $lang_id);
            if (empty($popular_posts)) {
                $popular_posts = $this->post_model->get_popular_posts(365, $lang_id);
                set_cache_data_by_lang('popular_posts_year', $popular_posts, $lang_id);
            }
        }
        $data = array(
            'result' => 0,
            'html_content' => "",
        );
        if (!empty($popular_posts)) {
            foreach ($popular_posts as $post) {
                $vars = array('post' => $post);
                $html_content .= '<ul class="popular-posts"><li>' . $this->load->view('post/_post_item_small', $vars, true) . '</li></ul>';
            }
            $data = array(
                'result' => 1,
                'html_content' => $html_content,
            );
        }
        echo json_encode($data);
    }

    /**
     * Add or Delete Reading List
     */
    public function add_delete_reading_list_post()
    {
        post_method();
        $post_id = clean_number($this->input->post('post_id'));
        $is_post_in_reading_list = $this->reading_list_model->is_post_in_reading_list($post_id);
        if ($is_post_in_reading_list == true) {
            $this->reading_list_model->delete_from_reading_list($post_id);
        } else {
            $this->reading_list_model->add_to_reading_list($post_id);
        }
    }
}
