# Documentação API

Essa API busca, através do CPF, informações de processos dos seguintes Tribunais Regionais Federais:

- **TRF1** - Implementado, Concluído mas passando por melhorias;
- **TRF2** - Implementado, parcialmente concluído;
- TRF3 - Não implementado;
- TRF4 - Implementado na Intranet, concluído fora do modelo atual;
- TRF5 - Não implementado.

#### Forma de instalação

- Linux:  

No terminal, vá até a pasta em que deseja instalar a API:

```bash 
# cd /var/www
# git clone https://github.com/PablNico/macohinAPI.git
# composer u
```

- Windows:

Baixe os arquivos do GitHub e com o CMD aberto na pasta desejada e com o Composer instalado:

``````bash
C:\xampp\htdocs\macohinAPI> composer u
``````

#### Forma de utilização

----

- TRF1

Por enquanto apenas a rota do TRF1 está implementada, contendo um *endpoint* que pode trazer três tipos de informações:

| Rota                              | Ação                                                         |
| --------------------------------- | ------------------------------------------------------------ |
| /trf1/cpf/*processo/{numCpf}*     | Retorna os dados: Número do processo, Nova numeração, Grupo, Assunto, Data de autuação, Órgão julgador, Juiz relator e Processo originário. |
| /trf1/cpf/*distribuicao/{numCpf}* | Retorna os dados referentes a distribuição: Data, Descrição, e Juiz. |
| /trf1/cpf/*movimentacao/{numCpf}* | Retorna os dados referentes a movimentação: Data, Código, Descrição, e Complemento. |

A busca por número de processo e número de processo originário ainda está em desenvolvimento. As informações são retornadas em formato JSON. 

- TRF2

| Rota                                  | Ação                                                         |
| ------------------------------------- | ------------------------------------------------------------ |
| /trf2/{estado}/*cpf/{numCpf}*         | Retorna os dados relacionados a **Capa do processo, Assuntos, Partes e representantes, informações adicionais** de determinado CPF e estado. |
| /trf2/{estado}/*numProceso/{numProc}* | Retorna os dados relacionados a **Capa do processo, Assuntos, Partes e representantes, informações adicionais** de determinado numero de processo e estado. |

