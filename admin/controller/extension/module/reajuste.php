<?php
/**
 * @since 09/01/2017
 */
class ControllerExtensionModuleReajuste extends Controller {
	private $error = array();
	protected $title = 'Reajuste de preços';

	public function index() {
		$this->document->setTitle($this->title);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!empty($this->request->post['opcao'])) {
				$opcao = 1;
			} else {
				$opcao = 0;
			}

			if (isset($this->request->post['sinal'])) {
				$sinal = $this->request->post['sinal'];
			} else {
				$sinal = '+';
			}

			$this->load->model('extension/module/reajuste');

			if ($opcao) {
				$this->model_extension_module_reajuste->updatePrice($this->request->post['price'], $this->request->post['category_id']);
			} else {
				if ($sinal == '+') {
					$new_price = (float)$this->request->post['porcentagem'];
				} else {
					$new_price = 0 - (float)$this->request->post['porcentagem'];
				}
				$this->model_extension_module_reajuste->updatePercent($new_price, $this->request->post['category_id']);
			}

			$this->session->data['success'] = 'Os preços dos produtos foram atualizados.';

			$this->response->redirect($this->url->link('extension/module/reajuste', 'token=' . $this->session->data['token'], true));
		}

		$data['heading_title'] = $this->title;

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => 'Módulos',
			'href'      => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->title,
			'href'      => $this->url->link('extension/module/reajuste', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/module/reajuste', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		$data['error'] = $this->error;

		if (!empty($this->error['error_warning'])) {
			$data['error_warning'] = $this->error['error_warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['token'] = $this->session->data['token'];

		if (isset($this->request->post['opcao'])) {
			$data['opcao'] = $this->request->post['opcao'];
		} else {
			$data['opcao'] = 0;
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} else {
			$data['price'] = '';
		}

		if (isset($this->request->post['sinal'])) {
			$data['sinal'] = $this->request->post['sinal'];
		} else {
			$data['sinal'] = '+';
		}

		if (isset($this->request->post['porcentagem'])) {
			$data['porcentagem'] = $this->request->post['porcentagem'];
		} else {
			$data['porcentagem'] = '';
		}

		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} else {
			$data['category_id'] = '';
		}

		$this->load->model('catalog/category');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(array());

		foreach ($categories as $category) {
			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name']
			);
		}

		$data['is_admin'] = ($this->user->getGroupId() == 1);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/reajuste', $data));
	}

	public function produtos() {
		$this->load->model('extension/module/reajuste');

		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$produtos = $this->model_extension_module_reajuste->getProductsByCategoryId($this->request->post['category_id']);

			$json['produtos'] = array();

			if (!empty($this->request->post['opcao'])) {
				$opcao = 1;
			} else {
				$opcao = 0;
			}

			if (isset($this->request->post['sinal'])) {
				$sinal = $this->request->post['sinal'];
			} else {
				$sinal = '+';
			}

			foreach ($produtos as $produto) {
				if ($opcao) {
					$new_price = $this->request->post['price'];
				} else {
					if ($sinal == '+') {
						$new_price = $produto['price'] + ($produto['price'] * ($this->request->post['porcentagem'] / 100));
					} else {
						$new_price = $produto['price'] - ($produto['price'] * ($this->request->post['porcentagem'] / 100));
					}
				}

				$json['produtos'][] = array(
					'name' => $produto['name'],
					'price' => $this->currency->format($produto['price'], $this->config->get('config_currency')),
					'new_price' => $this->currency->format($new_price, $this->config->get('config_currency'))
				);
			}

			if (!$produtos) {
				$json['mensagem'] = 'Não há produtos neste departamento.';
			}
		}

		if ($this->error) {
			$json['mensagem'] = array_shift($this->error);
		}

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/reajuste')) {
			$this->error['warning'] = 'Você não tem permissão para modificar este módulo.';
		}

		if (empty($this->request->post['category_id'])) {
			$this->error['error_category'] = 'Selecione um departamento.';
		}

		if (!empty($this->request->post['opcao']) && !isset($this->request->post['price'])) {
			$this->error['error_price'] = 'Digite um preço fixo.';
		}

		if (empty($this->request->post['opcao']) && empty($this->request->post['porcentagem'])) {
			$this->error['error_porcentagem'] = 'Digite um valor de porcentagem.';
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
