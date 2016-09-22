FROM elasticsearch

RUN apt-get update

RUN apt-get install -y joe

RUN /usr/share/elasticsearch/bin/plugin install mobz/elasticsearch-head

RUN /usr/share/elasticsearch/bin/plugin install royrusso/elasticsearch-HQ

RUN /usr/share/elasticsearch/bin/plugin install lmenezes/elasticsearch-kopf

CMD ["elasticsearch"]
