dados <- read.table(text = "Modo	Carga	Average	Min	Max	Std. Dev.	Error %	Throughput	Received KB/sec	Sent KB/sec	Avg. Bytes	Time (sec)	Ramp-up (sec)
Síncrono	Média	23600	1504	46009	11300.14	0.000%	15.51944	8992.13	27.46	593316.6	57	20
Síncrono	Média	22769	1277	46731	11210.34	0.000%	15.82125	9169.54	28.00	593481.0	57	20
Síncrono	Média	22893	1294	47774	11446.61	0.000%	15.34914	8895.92	27.16	593481.0	59	20
Síncrono	Média	21949	1164	43456	10649.02	0.000%	16.40229	9506.30	29.02	593481.0	55	20
Síncrono	Média	22522	1175	47063	11696.66	0.000%	15.57883	9029.04	27.57	593481.0	58	20
Síncrono	Média	22927	1095	45807	11492.11	0.000%	15.54457	9006.69	27.51	593316.6	58	20
Assíncrono	Média	15195	733	29859	8406.12	0.000%	29.55378	3.61	52.30	125.0	30	1
Assíncrono	Média	15301	712	29860	8422.58	0.000%	29.55186	3.61	52.29	125.0	30	1
Assíncrono	Média	15172	702	29571	8311.54	0.000%	29.83219	3.64	52.79	125.0	30	1
Assíncrono	Média	15267	732	29868	8437.35	0.000%	29.51647	3.60	52.23	125.0	30	1
Assíncrono	Média	15309	712	29967	8448.24	0.000%	29.44975	3.59	52.11	125.0	31	1
Assíncrono	Média	15436	712	29962	8417.78	0.000%	29.45546	3.60	52.12	125.0	31	1
Síncrono	Alta	2601	1019	35402	3210.51	0.000%	16.21963	9400.07	28.70	593458.3	407	400
Síncrono	Alta	3198	1023	43286	4611.17	0.000%	16.10761	9335.15	28.50	593458.3	410	400
Síncrono	Alta	3367	1013	53061	7010.84	0.000%	14.92831	8651.69	26.42	593458.3	442	400
Síncrono	Alta	2101	1026	19055	1496.36	0.000%	16.29218	9442.12	28.83	593458.3	405	400
Síncrono	Alta	3723	1029	33410	4470.75	0.000%	16.28765	9439.49	28.82	593458.3	405	400
Síncrono	Alta	3192	1043	27499	3086.45	0.000%	16.28588	9438.47	28.82	593458.3	405	400
Assíncrono	Alta	11028	597	24199	6665.23	0.000%	29.51341	3.60	52.22	125.0	224	200
Assíncrono	Alta	10289	451	22520	6169.40	0.000%	29.71641	3.63	52.58	125.0	222	200
Assíncrono	Alta	11046	498	23886	6637.17	0.000%	29.53200	3.60	52.26	125.0	223	200
Assíncrono	Alta	10369	567	22385	6018.44	0.000%	29.75373	3.63	52.65	125.0	222	200
Assíncrono	Alta	9971	484	22091	6056.70	0.000%	29.77209	3.63	52.68	125.0	222	200
Assíncrono	Alta	9930	486	22375	5994.74	0.000%	29.73326	3.63	52.61	125.0	222	200
", sep = "\t", header = TRUE)

head(dados)

dados$Modo <- factor(dados$Modo)
dados$Carga <- factor(dados$Carga)
dados$Ramp.up <- as.numeric(dados$Ramp.up..sec.)
dados$Time <- as.numeric(dados$Time..sec.)
dados$Error.. <- as.numeric(gsub("%", "", dados$Error..))
dados$Throughput <- as.numeric(dados$Throughput)
dados$ErroPresente = ifelse(dados$Error.. > 0, TRUE, FALSE)

anova_throughput <- aov(Throughput ~ Modo * Carga, data = dados)
summary(anova_throughput)

library(ggplot2)
library(ggpubr)
library(dplyr)

my_comparisons_modo <- list(c("Síncrono", "Assíncrono"))
my_comparisons_carga <- list(c("Média", "Alta"))

ggboxplot(dados, x = "Modo", y = "Throughput",
          color = "Carga", palette = "jco", add = "jitter",
          ylab = "Throughput (req/s)", xlab = "Modo") +
  stat_compare_means(comparisons = my_comparisons_modo, label = "p.signif",  method = "t.test",
                     label.y = max(dados$Throughput) * 1.1) +
  stat_compare_means(aes(group = Carga), label = "p.signif", method = "t.test",
                     label.y = max(dados$Throughput) * 1.2) +
  stat_compare_means(method = "anova", label.y = max(dados$Throughput) * 1.3) +
  theme_light()
